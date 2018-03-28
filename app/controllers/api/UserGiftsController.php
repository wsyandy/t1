<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 09/01/2018
 * Time: 22:35
 */

namespace api;

class UserGiftsController extends BaseController
{
    function indexAction()
    {
        $user_id = $this->params('user_id');
        $car_gift = $this->params('car_gift', 0); //座驾礼物
        $common_gift = $this->params('common_gift', 0); //普通礼物

        if (isBlank($user_id)) {
            $user_id = $this->currentUserId();
        }

        $user = \Users::findFirstById($user_id);

        $total_gift_num = $user->getReceiveGiftNum();
        $opts = [];

        if (!$car_gift && !$common_gift) {
            $conds = ['conditions' => 'user_id = ' . $user_id, 'order' => 'amount desc'];

            $page = $this->params('page', 1);
            $per_page = $this->params('per_page', 100);

            $user_gifts = \UserGifts::findPagination($conds, $page, $per_page);

            $opts = $user_gifts->toJson('user_gifts', 'toJson');

        } else {

            if ($car_gift) {
                $opts['user_car_gifts'] = \UserGifts::searchCarGifts($user_id);
            }

            if ($common_gift) {
                $opts['user_gifts'] = \UserGifts::searchCommonGifts($user_id);
            }
        }

        $opts = array_merge(['total_gift_num' => $total_gift_num], $opts);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $opts);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 15/01/2018
 * Time: 15:36
 */

namespace m;

class UsersController extends BaseController
{
    function indexAction()
    {

    }

    function accountAction()
    {
        $products = \Products::findDiamondListByUser($this->currentUser());
        $payment_channels = \PaymentChannels::selectByUser($this->currentUser());

        $selected_product = $products[0];
        $selected_payment_channel = $payment_channels[0];
        $this->view->selected_product = $selected_product;
        $this->view->selected_payment_channel = $selected_payment_channel;
        $this->view->products = $products;
        $this->view->user = $this->currentUser();
        $this->view->payment_channels = $payment_channels;
        $this->view->product_channel = $this->currentProductChannel();
        $this->view->title = '我的账户';
    }

    function levelIntroduceAction()
    {
        $code = $this->params('code');
        $file_name = $code . '_level_introduce';
        $file_path = APP_ROOT . 'app/views/m/users/' . $file_name . '.volt';
        if (file_exists($file_path)) {
            $this->pick('m/users/' . $file_name);
            return;
        }

        $this->view->title = 'Hi荣耀等级介绍';
    }

    function levelInfoAction()
    {
        $user_id = $this->params('user_id');
        $current_user = $this->currentUser();
        $show_upgrade_official = true;
        if ($user_id) {
            $show_upgrade_official = false;
        }

        $level = $current_user->level;
        $segment_text = $current_user->segment_text;
        $need_experience = $current_user->next_level_experience - $current_user->experience;
        $skip_url = '/m/users/ruanyuyin_level_introduce';

        $this->view->level = $level;
        $this->view->segment_text = $segment_text;
        $this->view->need_experience = round($need_experience);
        $this->view->show_upgrade_official = $show_upgrade_official;
        $this->view->skip_url = $skip_url;
    }


    function recommendAction()
    {
        $this->view->title = "感兴趣的人";

        $code = $this->params('code');
        $sid = $this->params('sid');

        $this->view->sid = $sid;
        $this->view->code = $code;
    }

    function ruanyuyinLevelIntroduceAction()
    {
        $this->view->title = '荣耀等级介绍';
    }

    function userListAction()
    {
        if ($this->request->isAjax()) {
            $current_user = $this->currentUser();
            $page = $this->params('page');
            $per_page = $this->params('per_page');
            $user_list = \Users::recommend($current_user, $page, $per_page);

            $res = [];
            if (count($user_list)) {

                $res = $user_list->toJson('user_list', 'toRecommendJson');
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
        }
    }

    function addFriendAction()
    {
        if ($this->request->isAjax()) {
            $current_user = $this->currentUser();
            $self_introduce = $this->params('self_introduce', '您好');
            $user_id = $this->params('user_id');

            $user = \Users::findFirstById($user_id);

            if (isBlank($user)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
            }

            debug($user->id, $self_introduce);

            $current_user->addFriend($user, ['self_introduce' => $self_introduce]);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }
    }
}
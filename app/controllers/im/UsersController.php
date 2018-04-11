<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 15/01/2018
 * Time: 15:36
 */

namespace im;

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
        $sid = $this->params('sid');
        $this->response->redirect('/m/users/level_detail?sid=' . $sid . "&code=" . $code);

        $this->view->title = '荣耀等级';

        $current_user = $this->currentUser();

        $level = $current_user->level;

        $segment = $current_user->segment;

        $segment_text = $current_user->segment_text;

        $need_experience = $current_user->next_level_experience - $current_user->experience;

        $this->view->code = $code;
        $this->view->sid = $sid;

        $this->view->level = $level;
        $this->view->segment = $segment;
        $this->view->segment_text = $segment_text;
        $this->view->need_experience = $need_experience;
    }

    function levelDetailAction()
    {
        $this->view->title = 'Hi荣耀等级介绍';
    }


    function recommendAction()
    {
        $this->view->title = "感兴趣的人";

        $code = $this->params('code');
        $sid = $this->params('sid');

        $this->view->sid = $sid;
        $this->view->code = $code;
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
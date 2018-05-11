<?php

namespace m;

class WishHistoriesController extends BaseController
{
    function indexAction()
    {

    }

    function refreshAction()
    {
        $page = $this->params('page');
        $per_page = 20;

        $product_channel_id = $this->currentProductChannelId();
        $key = \WishHistories::getGuardWishKey($product_channel_id);
        $wish_histories = \WishHistories::findByRelationsForWish($key, $page, $per_page);
        if (!$wish_histories) {
            return $this->renderJSON(ERROR_CODE_FAIL, '没有更多的愿望哦，快来许愿吧！');
        }
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $wish_histories->toJson('wish_histories', 'toSimpleJson'));

    }

    function releaseWishAction()
    {
        $user = $this->currentUser();
        $wish_text = $this->params('my_wish_text');
        if (!$wish_text) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }
        $amount = 5;
        if ($user->diamond < $amount) {
            return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
        }

        $product_channel_id = $this->currentProductChannelId();
        $opts = [
            'user_id' => $user->id,
            'amount' => $amount,
            'wish_text' => $wish_text,
            'product_channel_id' => $product_channel_id
        ];
        $result = \WishHistories::createWishHistories($opts);
        if ($result) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '发布成功！');
        }
    }

    function myWishHistoriesAction()
    {
        $product_channel_id = $this->currentProductChannelId();
        $my_wish_histories = \WishHistories::find(['conditions' => 'user_id = ' . $this->currentUserId(), 'order' => 'id desc']);

        $my_wish_datas = [];
        foreach ($my_wish_histories as $my_wish_history) {
            $num_text = [];
            $guard_number = \WishHistories::getGuardedNumber($product_channel_id, $my_wish_history->id);
            $wish_text = $my_wish_history->wish_text;
            array_push($num_text,$guard_number,$wish_text);
            array_push($my_wish_datas,$num_text);
        }
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['my_wish_datas' => $my_wish_datas]);
    }

    function guardWishAction()
    {
        $user = $this->currentUser();
        $wish_history_id = $this->params('wish_history_id');
        $amount = 2;
        if ($user->diamond < $amount) {
            return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
        }
        $opts = ['remark' => '守护愿望消耗' . $amount . '钻石', 'target_id' => $wish_history_id];
        $result = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_GUARD_WISH_EXPENSES, $amount, $opts);
        if (!$result) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '参数错误');
        }

        $product_channel_id = $this->currentProductChannelId();
        $user_db = \Users::getUserDb();
        $guard_wish_key = \WishHistories::getGuardWishKey($product_channel_id);
        $guarded_number = $user_db->zincrby($guard_wish_key, 1, $wish_history_id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '守护成功', ['guarded_number' => $guarded_number]);
    }

    function winningRecordAction()
    {
        $product_channel_id = $this->currentProductChannelId();
        $key = \WishHistories::getGuardWishKey($product_channel_id);
        $lucky_names = \WishHistories::getRand($key);

        $this->view->lucky_names = $lucky_names;
    }

    function wishingTreeListAction()
    {
        $product_channel_id = $this->currentProductChannelId();
        $key = \WishHistories::getGuardWishKey($product_channel_id);
        $UserIdGuardeds = \WishHistories::getUserIdGuarded($key, 20);

        $tree_list = [];
        foreach ($UserIdGuardeds as $UserIdGuarded) {
            $user = \Users::findById($UserIdGuarded['user_id']);
            $tree_list[] = array(
                'user_id' => $UserIdGuarded['user_id'],
                'guarded_number' => $UserIdGuarded['guarded_number'],
                'sex' => $user->sex == 1 ? "man" : "woman" ,
                'avatar' =>$user->avatar_url,
                'nickname' => $user->nickname,
                'age' => $user->age,
            );
        }
        //return $this->renderJSON(ERROR_CODE_SUCCESS, '守护成功', ['tree_list' => $tree_list]);
        $this->view->tree_list = $tree_list;
    }

    function searchUserAction()
    {
        $uid = $this->params('param');
        $user = \Users::findByConditions(array('uid'=>$uid));
        $user = $user->toJson('user');
        $user = $user['user'][0];

        $wish_histories = \WishHistories::findByConditions(array('user_id'=>$user['id']));
        $wish_histories = $wish_histories->toJson('$wish_histories');
        //$wish_histories = $wish_histories['$wish_histories'][0];



        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['wish_histories' => $wish_histories]);

    }

}
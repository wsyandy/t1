<?php

namespace m;

class WishHistoriesController extends BaseController
{
    function indexAction()
    {

    }

    function refreshAction()
    {
        $per_page = 20;
        $product_channel_id = $this->currentProductChannelId();
        $key = \WishHistories::getGuardWishKey($product_channel_id);

        $wish_histories = \WishHistories::findByRelationsForWish($key, $per_page);
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
            return $this->renderJSON(ERROR_CODE_FAIL, '请添加您的愿望！');
        }

        $product_channel_id = $this->currentProductChannelId();
        $opts = [
            'user_id' => $user->id,
            'wish_text' => $wish_text,
            'product_channel_id' => $product_channel_id
        ];

        $cond = ['conditions' => 'user_id=:user_id: and product_channel_id=:product_channel_id:',
            'bind' => ['user_id' => $user->id, 'product_channel_id' => $product_channel_id]
        ];
        $wish_history_num = \WishHistories::count($cond);
        if ($wish_history_num >= 3) {
            return $this->renderJSON(ERROR_CODE_FAIL, '每个人只能许三个愿望，不能太贪心哦！');
        }

        $result = \WishHistories::createWishHistories($opts);
        if ($result) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '发布成功！');
        }
        return $this->renderJSON(ERROR_CODE_FAIL, '发布失败');
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
            array_push($num_text, $guard_number, $wish_text);
            array_push($my_wish_datas, $num_text);
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
        $result = \AccountHistories::changeBalance($user, ACCOUNT_TYPE_GUARD_WISH_EXPENSES, $amount, $opts);
        if (!$result) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '参数错误');
        }

        //守护愿望只加经验，段位和财富值
        $this->currentUser()->updateExperienceForWish($amount);

        $product_channel_id = $this->currentProductChannelId();
        $user_db = \Users::getUserDb();
        $guard_wish_key = \WishHistories::getGuardWishKey($product_channel_id);
        $guarded_number = $user_db->zincrby($guard_wish_key, 1, $wish_history_id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '守护成功', ['guarded_number' => $guarded_number]);
    }

    function winningRecordAction()
    {
        $lucky_names = \WishHistories::getLuckyUserList();

        $this->view->lucky_names = json_encode($lucky_names,JSON_UNESCAPED_UNICODE);
    }

    function wishingTreeListAction()
    {
        $product_channel_id = $this->currentProductChannelId();
        $key = \WishHistories::getGuardWishKey($product_channel_id);
        $user_db = \Users::getUserDb();
        $res = $user_db->zrevrange($key, 0, 19, 'withscores');
        $ids = array_keys($res);
        $wish_history_lists = \WishHistories::findByIds($ids);
        $wish_histories = [];
        foreach ($wish_history_lists as $wish_history_list) {
            $wish_histories[] = $wish_history_list->toSimpleJson();
        }

        $this->view->wish_histories = json_encode($wish_histories, JSON_UNESCAPED_UNICODE);
    }

    function searchUserAction()
    {
        $uid = $this->params('uid');
        $user = \Users::findFirstByUid($uid);
        if (!$user) {
            return $this->renderJSON(ERROR_CODE_FAIL, '没有这个用户哦！');
        }

        $show_wish_histories = \WishHistories::findBy(['user_id' => $user->id]);
        if ($show_wish_histories) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $show_wish_histories->toJson('show_wish_histories', 'toSimpleJson'));
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '当前用户暂时还没好友发布愿望哦！');
    }

    function showWishAction()
    {
        $id = $this->params('id');
        $wish_history = \WishHistories::findByIds($id);
        if ($wish_history) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $wish_history->toJson('show_wish_histories', 'toSimpleJson'));
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
    }

}
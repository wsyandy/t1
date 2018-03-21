<?php
/**
 * Created by PhpStorm.
 * User: administrator
 * Date: 2018/1/19
 * Time: 下午7:52
 */

namespace api;

class SharesController extends BaseController
{
    function goldWorksAction()
    {
        $user = $this->currentUser();
        $share_json = [];
        foreach ([SHARE_TYPE_WEIXIN => '微信好友', SHARE_TYPE_WEIXIN_CIRCLE => '微信朋友圈', SHARE_TYPE_QQ => 'QQ好友',
                     SHARE_TYPE_QZONE => 'QQ空间', SHARE_TYPE_SINA => '新浪微博'] as $key => $value) {
            $type = $key;
            $name = $value;
            $status = $user->ShareTaskStatus($type);
            $gold = $user->ShareTaskGold();
            $share_json[] = ['name' => $name, 'type' => $type, 'work_status' => $status, 'work_gold' => $gold];
        }

        $opts = ['gold' => $user->gold, 'sign_in_status' => $user->sign_in_status, 'sign_in_message' => $user->sign_in_message, 'gold_works' => $share_json];
        $this->renderJSON(ERROR_CODE_SUCCESS, '', $opts);
    }

    function detailAction()
    {
        $room_id = $this->params('room_id');
        $share_source = $this->params('share_source');

        $user = $this->currentUser();
        $image_url = $user->avatar_small_url;
        $image_small_url = $this->currentProductChannel()->avatar_url;
        $description = "Hi—很好玩的语音直播软件，连麦聊天，组队开黑哦";

        $opts = [
            'user_id' => $user->id,
            'product_channel_id' => $this->currentProductChannelId(),
            'share_source' => $share_source,
            'data' => ['room_id' => $room_id]
        ];
        $share_history = \ShareHistories::createShareHistory($opts);

        $url = $share_history->getShareUrl($this->getRoot());

        $res = [
            'title' => $user->nickname . "正在这个房间玩，快来一起连麦嗨！",
            'image_url' => $image_url,
            'image_small_url' => $image_small_url,
            'description' => $description,
            'url' => $url,
            'share_history_id' => $share_history->id
        ];
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
    }

    function resultAction()
    {
        $share_history = \ShareHistories::findFirstById($this->params('share_history_id', 0));
        if (!$share_history) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $type = intval($this->params('type', 0));
        if (!$type || !array_key_exists($type, \ShareHistories::$TYPE)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $status = intval($this->params('status', 0));
        if (!$status || !array_key_exists($status, \ShareHistories::$STATUS)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $share_history->type = $type;
        $share_history->status = $status;

        $share_history->save();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

}
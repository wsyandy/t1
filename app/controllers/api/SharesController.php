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

    function detailAction()
    {
        $room_id = $this->params('room_id');
        $user = $this->currentUser();
        $image_url = $user->avatar_small_url;
        $image_small_url = "http://yiyuan-development.img-cn-hangzhou.aliyuncs.com/chance/product_channels/avatar/5a5c0e601d994.png@!small";
        $description = "H是目前最稳定、最火爆的语音交友社区,快来跟我一起玩吧！";
        $url = "www.baidu.com";

        $opts = [
            'user_id' => $user->id,
            'product_channel_id' => $this->currentProductChannelId(),
            'share_source' => '房间',
            'data' => $room_id
        ];
        $share_history = \ShareHistories::createShareHistory($opts);

        $res = [
            'title' => $user->nickname . "正在邀请你一起连麦",
            'image_url' => $image_url,
            'image_small_url' => $image_small_url,
            'description' => $description,
            'url' => $url,
            'id' => $share_history->id
        ];
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
    }

    function resultAction()
    {
        $share_history = \ShareHistories::findFirstById($this->params('id', 0));
        if (!$share_history) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $type = $this->params('type');
        if (!$type && !array_key_exists($type, \ShareHistories::$TYPE)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $status = $this->params('status');

        $share_history->type = $type;
        $share_history->status = $status;

        $share_history->save();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

}
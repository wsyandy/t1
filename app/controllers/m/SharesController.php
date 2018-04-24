<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/24
 * Time: 下午3:04
 */
namespace m;

class SharesController extends BaseController
{
    function createAction()
    {
        $room_id = $this->params('room_id');
        $share_source = 'H5';

        $user = $this->currentUser();
        $code = $this->currentProductChannel()->code;

        $opts = [
            'user_id' => $user->id,
            'product_channel_id' => $this->currentProductChannelId(),
            'share_source' => $share_source,
            'data' => ['room_id' => $room_id]
        ];
        $share_history = \ShareHistories::createShareHistory($opts);

        $image_url = $user->avatar_small_url;

        $image_small_url = $this->currentProductChannel()->avatar_url;

        if ($share_history->isGoldWorks()) {
            $image_url = $image_small_url;
        }
        $product_channel_name = $this->currentProductChannel()->name;

        $description = $product_channel_name . "—很好玩的语音直播软件，连麦聊天，组队开黑哦";

        $url = $share_history->getShareUrl($this->getRoot(), $code);
        $title = $share_history->getShareTitle($user->nickname, $product_channel_name);

        $res = [
            'title' => $title,
            'image_url' => $image_url,
            'image_small_url' => $image_small_url,
            'description' => $description,
            'url' => $url,
            'share_history_id' => $share_history->id
        ];
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
    }

    function testAction()
    {
        $code = $this->params('code');
        $sid = $this->params('sid');

        $this->view->code = $code;
        $this->view->sid = $sid;
    }
}
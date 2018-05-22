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
        $share_source = $this->params('share_source', 'H5');
        $type = $this->params('type');
        $platform = $this->params('platform');
        $image_data = $this->params('image_data', '');
        $action = $this->params('action', '');

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

        if ($action == 'share_image') {
            $image_url = \Users::getImageForShare($image_data);
        }
        $product_channel_name = $this->currentProductChannel()->name;

        //$description = $product_channel_name . "—很好玩的语音直播软件，连麦聊天，组队开黑哦";
        $description = $share_history->getShareDescription($product_channel_name);
        $url = $share_history->getShareUrl($this->getRoot(), $code);
        $title = $share_history->getShareTitle($user->nickname, $product_channel_name);

        $test_url = "app://share?platform=" . $platform . "&type=" . $type;

        switch ($type) {
            case 'web_page': {
                $test_url .= "&title=" . $title . "&description=" . $description .
                    "&share_url=" . $url . "&image_url=" . $image_url . "&share_history_id=" . $share_history->id;
                break;
            }
            case 'image': {
                $test_url .= "&image_url=" . $image_url . "&share_history_id=" . $share_history->id;
                break;
            }
            case 'text': {
                $test_url .= "&description=" . $description . "&share_history_id=" . $share_history->id;
                break;
            }
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['test_url' => $test_url]);
    }

    function testAction()
    {
        $code = $this->params('code');
        $sid = $this->params('sid');

        $this->view->code = $code;
        $this->view->sid = $sid;
    }
}
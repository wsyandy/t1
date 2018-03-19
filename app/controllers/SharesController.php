<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/27
 * Time: 上午10:19
 */
class SharesController extends ApplicationController
{
    function indexAction()
    {
        $share_history = \ShareHistories::findFirstById($this->params('share_history_id', 0));

        if (!$share_history) {
            echo "参数错误";
            return false;
        }

        $share_history->increase('view_num');
        $user = $share_history->user;

        $user_agent = $this->request->getUserAgent();
        debug($user_agent);

        $platform = 'android';
        if (preg_match('/ios|iphone|ipad/i', $user_agent)) {
            $platform = 'ios';
        }

        $soft_version = \SoftVersions::findFirst([
            'conditions' => 'product_channel_id=:product_channel_id: and platform=:platform: and channel_package = 0',
            'bind' => ['product_channel_id' => $user->product_channel_id, 'platform' => $platform],
            'order' => 'id desc'
        ]);

        $soft_version_id = 0;

        if ($soft_version) {
            $soft_version_id = $soft_version->id;
        }

        $data = $share_history->data;
        $room_id = '';

        if ($data) {
            $data = json_decode($data, true);
            $room_id = fetch($data, 'room_id');
        }

        $this->view->user = $user;
        $this->view->room_id = $room_id;
        $this->view->soft_version_id = $soft_version_id;
    }
}
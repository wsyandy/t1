<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/28
 * Time: 上午10:47
 */

namespace iapi;

class RoomsController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 8);
        $hot = intval($this->params('hot', 0));
        $new = intval($this->params('new', 0));
        $product_channel_id = $this->currentProductChannelId();

        $opts = ['product_channel_id' => $product_channel_id, 'hot' => $hot, 'new' => $new];

        $rooms = \Rooms::searchRooms($opts, $page, $per_page);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $rooms->toJson('rooms', 'toSimpleJson'));
    }
}
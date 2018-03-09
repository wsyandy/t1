<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/9
 * Time: 下午6:02
 */
namespace admin;
class RoomEarningsController extends BaseController
{
    function indexAction()
    {
        $cond = $this->getConditions('room');
        $name = $this->params('name');

        if (isset($cond['conditions'])) {
            $cond['conditions'] .= " and user_id > 0";
        } else {
            $cond['conditions'] = " user_id > 0";
        }

        if ($name) {
            $cond['conditions'] .= " and name like '%$name%' ";
        }

        $page = 1;
        $total_page = 1;
        $per_page = 30;
        $total_entries = $total_page * $per_page;
        $cond['order'] = "id desc";

        $rooms = \Rooms::findPagination($cond, $page, $per_page, $total_entries);

        $this->view->rooms = $rooms;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }
}
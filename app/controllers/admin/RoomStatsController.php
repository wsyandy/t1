<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/3
 * Time: 下午12:00
 */

namespace admin;

class RoomStatsController extends BaseController
{
    function totalStatAction()
    {
        $cond = $this->getConditions('room');

        $page = $this->params('page', 1);

        $per_page = $this->params('per_page', 20);

        $rooms = \Rooms::roomIncomeList($page, $per_page, $cond);

        $this->view->rooms = $rooms;
    }

    function totalStatDetailAction()
    {
        $room_id = $this->params('id');
        $room = \Rooms::findFirstById($room_id);

        //每月天数数组array('d'=>'Y-m-d')
        $year = $this->params('year', date('Y'));
        $month = $this->params('month', date('m'));
        $stat_date = strtotime($year . "-" . $month . "-01");
        $end_at = endOfMonth($stat_date);
        $month_max_day = date('d', $end_at);//获取当前月份最大的天数

        $year_array = [];

        for ($i = date('Y'); $i >= 2018; $i--) {
            $year_array[$i] = $i;
        }

        for ($i = 1; $i <= $month_max_day; $i++) {

            if ($i < 10) {
                $day = "0" . $i;
            } else {
                $day = $i;
            }

            $day = $year . "-" . $month . "-" . $day;

            $start_at = beginOfDay(strtotime($day));
            $end_at = endOfDay(strtotime($day));

            $results[date('Ymd', $start_at)] = $room->getDayAmount($start_at, $end_at);
        }


        $this->view->room_id = $room_id;
        $this->view->results = $results;
        $this->view->year_array = $year_array;
        $this->view->month = intval($month);
        $this->view->year = intval($year);
        $this->view->room_id = $room_id;
    }

    function dayStatAction()
    {
        $date = $this->params('date', date('Y-m-d'));
        $room_id = $this->params('id');
        $user_id = $this->params('user_id');
        $stat_at = date("Ymd", strtotime($date));

        if ($user_id) {
            $user = \Users::findFirstById($user_id);

            if ($user) {
                $room_id = $user->room_id;
            }
        }

        if ($room_id) {
            $rooms = \Rooms::findPagination(['conditions' => 'id = ' . $room_id], 1, 1);
        } else {
            $rooms = \Rooms::dayStatRooms($stat_at);
        }

        foreach ($rooms as $room) {
            $room->day_income = $room->getDayIncome($stat_at);
            $room->day_enter_room_user = $room->getDayEnterRoomUser($stat_at);
            $room->day_send_gift_user = $room->getDaySendGiftUser($stat_at);
            $room->day_send_gift_num = $room->getDaySendGiftNum($stat_at);
            $room->day_send_gift_average_num = $room->daySendGiftAverageNum();
            $room->day_audience_time_text = secondsToText($room->getDayUserTime('audience', $stat_at));
            $room->day_broadcaster_time_text = secondsToText($room->getDayUserTime('broadcaster', $stat_at));
            $room->day_host_broadcaster_time_text = secondsToText($room->getDayUserTime('host_broadcaster', $stat_at));
        }

        $this->view->rooms = $rooms;
        $this->view->date = $date;
        $this->view->room_id = $room_id ? $room_id : '';
        $this->view->user_id = $user_id ? $user_id : '';
    }
}
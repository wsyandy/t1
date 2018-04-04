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
        $start_date = $this->params('start_date', date('Y-m-d'));
        $end_date = $this->params('end_date', date('Y-m-d'));
        $room_id = $this->params('room_id');
        $user_id = $this->params('user_id');
        $union_id = $this->params('union_id');

        $begin = beginOfDay(strtotime($start_date));
        $end = endOfDay(strtotime($end_date));


        $stat_at = date("Ymd", strtotime($start_date));

        if ($user_id) {
            $user = \Users::findFirstById($user_id);

            if ($user) {
                $room_id = $user->room_id;
            }
        }

        if ($room_id) {
            $rooms = \Rooms::findPagination(['conditions' => 'id = ' . $room_id], 1, 1);
        } elseif ($union_id) {
            $rooms = \Rooms::findPagination(['conditions' => 'union_id = ' . $union_id], 1, 500);
        } else {
            $rooms = \Rooms::dayStatRooms($stat_at);
        }

        foreach ($rooms as $room) {

            $room->total_income = 0;
            $room->total_enter_room_user = 0;
            $room->total_send_gift_user = 0;
            $room->total_send_gift_num = 0;
            $room->total_audience_time = 0;
            $room->total_broadcaster_time = 0;
            $room->total_host_broadcaster_time = 0;

            for ($date = $begin; $date <= $end; $date += 86400) {
                $stat_at = date("Ymd", $date);
                debug($stat_at);
                $room->total_income += $room->getDayIncome($stat_at);
                $room->total_enter_room_user += $room->getDayEnterRoomUser($stat_at);
                $room->total_send_gift_user += $room->getDaySendGiftUser($stat_at);
                $room->total_send_gift_num += $room->getDaySendGiftNum($stat_at);
                $room->total_audience_time += $room->getDayUserTime('audience', $stat_at);
                $room->total_broadcaster_time += $room->getDayUserTime('broadcaster', $stat_at);
                $room->total_host_broadcaster_time += $room->getDayUserTime('host_broadcaster', $stat_at);
            }

            $room->total_send_gift_average_num = $room->totalSendGiftAverageNum();
            $room->total_audience_time_text = secondsToText($room->total_audience_time);
            $room->total_broadcaster_time_text = secondsToText($room->total_broadcaster_time);
            $room->total_host_broadcaster_time_text = secondsToText($room->total_host_broadcaster_time);
        }

        $total_rooms = [];

        foreach ($rooms as $room) {
            $total_rooms[] = $room;
        }

        usort($total_rooms, function ($a, $b) {

            if ($a->total_income == $b->total_income) {
                return 0;
            }

            return $a->total_income > $b->total_income ? -1 : 1;
        });

        $this->view->rooms = $total_rooms;
        $this->view->start_date = $start_date;
        $this->view->end_date = $end_date;
        $this->view->union_id = $union_id;
        $this->view->stat_fields = ['房间ID', '名称', '房主信息', '家族ID', '进入房间人数', '钻石流水', '送钻石礼物人数', '送钻石礼物个数',
            '人均送钻石礼物个数', '房主时长', '主播时长', '旁听时长'];
        $this->view->room_id = $room_id ? $room_id : '';
        $this->view->user_id = $user_id ? $user_id : '';
    }
}
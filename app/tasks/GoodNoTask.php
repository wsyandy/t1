<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 15:17
 */
class GoodNoTask extends \Phalcon\Cli\Task
{

    function isGoodNo($num)
    {
        // 由3个以内数字组成的号码
        $num_array = array_unique(str_split($num));
        if (count($num_array) <= 3) {
            //echoLine('good 3', $num);
            return true;
        }

        //匹配6位以上递增
        if (preg_match('/(?:0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){5}\\d/', $num)) {
            //echoLine('匹配6位以上递增', $num);
            return true;
        }
        // 匹配6位以上递降
        if (preg_match('/(?:9(?=8)|8(?=7)|7(?=6)|6(?=5)|5(?=4)|4(?=3)|3(?=2)|2(?=1)|1(?=0)){5}\\d/', $num)) {
            //echoLine('匹配6位以上递降', $num);
            return true;
        }

        // 匹配4-9位连续的数字
        if (preg_match('/(?:(?:0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){3,}|(?:9(?=8)|8(?=7)|7(?=6)|6(?=5)|5(?=4)|4(?=3)|3(?=2)|2(?=1)|1(?=0)){3,})\\d/', $num)) {
            //echoLine('匹配4-9位连续的数字', $num);
            return true;
        }

        //匹配3位以上的重复数字
        if (preg_match("/([\\d])\\1{2,}/", $num)) {
            //echoLine('匹配3位以上的重复数字', $num);
            return true;
        }

        //AABB
        if (preg_match('/^\\d{0,3}(\\d)\\1(\\d)\\2\\d{0,3}$/', $num)) {
            //echoLine('AABB ',$num);
            return true;
        }

        // AAABBB
        if (preg_match('/^\\d{0,3}(\\d)\\1\\1(\\d)\\2\\2\\d{0,3}$/', $num)) {
            //echoLine('AAABBB', $num);
            return true;
        }

        // ABCABC
        if (preg_match('/^\\d{0,3}(\\d)(\\d)(\\d)\\1\\2\\3\\d{0,3}$/', $num)) {
            //echoLine('ABCABC', $num);
            return true;
        }

        if (preg_match("/^(520|1314|2018)/", $num)) {
            //echoLine('good 开头520|1314', $num);
            return true;
        }

        if (preg_match("/(1314)$/", $num)) {
            //echoLine('good 结尾1314', $num);
            return true;
        }

        return false;
    }

    function initGoodNoAction($params)
    {

        $i = $params[0];
        $user_db = Users::getUserDb();
        $not_good_no_uid = 'user_not_good_no_uid_list';
        $user_db->zadd($not_good_no_uid, $i, $i);

        $not_good_no_uid = 'room_not_good_no_uid_list';
        $user_db->zadd($not_good_no_uid, $i, $i);
    }

    // user room
    function generateNoAction($params)
    {

        $type = $params[0];
        if (!$type) {
            echoLine($params);
            return;
        }

        $user_db = Users::getUserDb();
        $good_no_uid = $type . '_good_no_uid_list';
        $not_good_no_uid = $type . '_not_good_no_uid_list';

        $good_max_id = $user_db->zrevrange($good_no_uid, 0, 0);
        $good_max_id = current($good_max_id);
        echoLine($good_no_uid, 'max', $good_max_id);

        $not_good_max_id = $user_db->zrevrange($not_good_no_uid, 0, 0);
        $not_good_max_id = current($not_good_max_id);
        echoLine($not_good_no_uid, 'max', $not_good_max_id);

        $min_id = $good_max_id > $not_good_max_id ? $good_max_id : $not_good_max_id;
        if ($min_id < 1000000) {
            $min_id = 1000000;
        }

        $min_max = $min_id + 1000000;
        echoLine('计算范围', $min_id, $min_max);

        $total = $user_db->zcard($not_good_no_uid);
        if ($total > 1000000) {
            echoLine('return', $not_good_no_uid, 'total', $total);
            return;
        }

        $count = 0;
        for ($i = $min_id; $i < $min_max; $i++) {
            if ($this->isGoodNo($i)) {
                $count++;
                $user_db->zadd($good_no_uid, $i, $i);
            } else {
                $user_db->zadd($not_good_no_uid, $i, $i);
            }
        }

        echoLine('count', $count);
    }

}
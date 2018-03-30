<?php

function mobileOperator($mobile)
{
    if (!isMobile($mobile)) {
        return 0;
    }

    $yi_dong = '/^(134|135|136|137|138|139|147|150|151|152|157|158|159|1705|178|182|183|184|187|188)/';
    $lian_tong = '/^(130|131|132|145|155|156|171|175|176|1709|185|186)/';
    $dian_xin = '/^(133|153|173|1700|177|180|181|189)/';

    $mobile_operator = 0;
    if (preg_match($yi_dong, $mobile)) {
        $mobile_operator = MOBILE_OPERATOR_CMCC;
    } elseif (preg_match($lian_tong, $mobile)) {
        $mobile_operator = MOBILE_OPERATOR_UNICOM;
    } elseif (preg_match($dian_xin, $mobile)) {
        $mobile_operator = MOBILE_OPERATOR_TELECOM;
    }
    return $mobile_operator;
}

// 请求协议
/**
 * @return string
 */
function getRequestProtocol()
{
    $di = \Phalcon\Di::getDefault()->get('config');
    $key = $di->request_protocol;
    return $key;
}

//获取毫秒时间戳
function millisecondTime()
{
    list($usec, $sec) = explode(' ', microtime());
    $usec2msec = intval($usec * 1000);
    $sec2msec = intval($sec * 1000);
    $time = $usec2msec + $sec2msec;
    return $time;
}

function secondsToText($seconds)
{
    $seconds = intval($seconds);

    if ($seconds <= 60) {
        return $seconds . '秒';
    }

    if ($seconds <= 60 * 60) {
        return intval($seconds / 60) . '分' . ($seconds % 60) . '秒';
    }

    $hour = intval($seconds / 3600);
    $minute = intval(($seconds % 3600) / 60);

    return $hour . '时' . $minute . '分' . ($seconds % 60) . '秒';
}

//字符串转为数组
function mbStrSplit($str)
{
    return preg_split('/(?<!^)(?!$)/u', $str);
}

function valueToStr($value)
{
    if ($value < 1000) {
        $value = intval($value * 100) / 100;
        return "$value";
    }

    $myriabit = intval($value / 10000);
    $kilobit = intval($value) / 1000 - $myriabit * 10;
    $kilobit = intval($kilobit);
    $res = $myriabit . '.' . $kilobit;
    $res = intval($res * 100) / 100;
    $str = $res;
    return $str . "万";
}

function beginOfWeek()
{
    $start = date("Ymd", strtotime("last sunday next day", time()));
    return beginOfDay(strtotime($start));
}

function endOfWeek()
{
    $end = date("Ymd", strtotime("next monday", time()) - 1);
    return endOfDay(strtotime($end));
}

function paramsToStr($params)
{

    $kv = [];
    foreach ($params as $k => $v) {
        if (in_array($k, ['_url', 'h', 'file'])) {
            continue;
        }
        $kv[] = $k . '=' . $v;
    }

    sort($kv);
    $source = implode('&', $kv);

    return $source;
}
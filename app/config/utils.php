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
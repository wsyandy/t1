<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/15
 * Time: 下午3:45
 */
class HotRoomHistories extends BaseModel
{
    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效', STATUS_PROGRESS => '申请中'];

    function generateTime($date, $hour)
    {
        $start_at = beginOfDay($date) + 60 * 60 * $hour;
        $end_at = $start_at + 60 * 60 * 2 - 1;
    }

    static function createHistories($opts, $applicant)
    {
        $user_id = fetch($opts, 'user_id');
        $start_at = fetch($opts, 'start_at');
        $introduce = fetch($opts, 'introduce');
        $union = $applicant->union;
        if (isBlank($union) || !$applicant->isUnionHost($union)) {
            return [ERROR_CODE_FAIL, '您没有权限申请'];
        }

        $user = \Users::findFirstById($user_id);

        if (isBlank($user) || $user->union_id != $union->id) {
            return [ERROR_CODE_FAIL, '此用户不存在或不在您家族中'];
        }

        if (isBlank($start_at) || $start_at < time()) {
            return [ERROR_CODE_FAIL, '选取时间段错误'];
        }

        if (isBlank($introduce) || mb_strlen($introduce) >= 50 || mb_strlen($introduce) <= 5) {
            return [ERROR_CODE_FAIL, '直播简介错误'];
        }

        $end_at = $start_at + 60 * 60 * 2 - 1;

        if (!self::checkTime($start_at, $end_at, $user_id)) {
            return [ERROR_CODE_FAIL, '已为该家族成员申请了此时间段，请换其它时间段'];
        }

        $history = new HotRoomHistories();
        $history->user_id = $user_id;
        $history->start_at = $start_at;
        $history->end_at = $end_at;
        $history->introduce = $introduce;
        $history->status = STATUS_PROGRESS;
        $history->union_id = $union->id;

        $history->save();

        return [ERROR_CODE_SUCCESS, '申请提交成功'];
    }

    static function checkTime($start_at, $end_at, $user_id)
    {
        $cond = [
            'conditions' => "user_id = :user_id: and status != :status: and start_at = :start_at: and end_at = :end_at:",
            "bind" => ['user_id' => $user_id, 'status' => STATUS_OFF, 'start_at' => $start_at, 'end_at' => $end_at]
        ];

        $history = HotRoomHistories::findFirst($cond);

        if (isPresent($history)) {
            return false;
        }

        return true;
    }
}
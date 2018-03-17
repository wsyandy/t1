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

    /**
     * @type Unions
     */
    private $_union;

    /**
     * @type Users
     */
    private $_user;

    function afterUpdate()
    {
        if ($this->hasChanged('status')) {
            if (STATUS_ON == $this->status) {
                //还需要将房间,时间段添加到热门房间
                Chats::sendTextSystemMessage($this->union->user_id, $this->successMessage());
            }
            if (STATUS_OFF == $this->status) {
                Chats::sendTextSystemMessage($this->union->user_id, $this->failMessage());
            }
        }
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

        if (!$user->room) {
            return [ERROR_CODE_FAIL, '此用户还没有创建房间'];
        }

        if (isBlank($start_at) || $start_at < time()) {
            return [ERROR_CODE_FAIL, '选取时间段错误'];
        }

        if (isBlank($introduce) || mb_strlen($introduce) > 50 || mb_strlen($introduce) < 5) {
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

        return [ERROR_CODE_SUCCESS, '申请提交成功，请耐心等待结果'];
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

    function successMessage()
    {
        $time = date('Y年m月d日H点', $this->start_at) . "-" . date('H点', $this->end_at + 1);

        $content = "您的上热门申请通过了，请通知" . $this->user->nickname . "(ID: $this->user_id)" .
            "在" . $time . "之间准时开播哦！我们会把" .
            $this->user->nickname . "的房间准时推荐到热门房间，快去准备吧";

        return $content;
    }

    function failMessage()
    {
        $content = "很遗憾，您的上热门申请未通过，您的申请时间段已被别的家族申请了，快去挑选其它的时间段吧！";

        return $content;
    }
}
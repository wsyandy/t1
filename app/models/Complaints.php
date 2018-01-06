<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/6
 * Time: 下午6:31
 */

class  Complaints extends BaseModel
{
    /**
     * @type Users
     */
    private $_complainer;
    /**
     * @type Users
     */
    private $_respondent;
    /**
     * @type Rooms
     */
    private $_room;

    static $STATUS = [VERIFY_WAIT => '等待处理', VERIFY_SUCCESS => '举报成功', VERIFY_FAIL => '举报失败'];

    static $COMPLAINT_TYPE = [1 => '色情', 2 => '骚扰', 3 => '不良信息', 4 => '广告'];

    static function createComplaint($complainer, $opts = [])
    {
        $room_id = fetch($opts, 'room_id', 0);
        $respondent_id = fetch($opts, 'respondent_id', 0);
        $complaint_type = fetch($opts, 'complaint_type');

        $complaint = new Complaints();
        $complaint->complainer_id = $complainer->id;

        if ($room_id) {
            $complaint->room_id = $room_id;
        }

        if ($respondent_id) {
            $complaint->respondent_id = $respondent_id;
        }

        $complaint->complaint_type = $complaint_type;
        $complaint->status = VERIFY_WAIT;

        $complaint->save();
    }
}
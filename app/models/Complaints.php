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

    static $STATUS = [AUTH_WAIT => '等待处理', AUTH_SUCCESS => '举报成功', AUTH_FAIL => '举报失败'];

    static $COMPLAINT_TYPE = [1 => '色情', 2 => '骚扰', 3 => '不良信息', 4 => '广告', 5 => '歌名不符合歌曲内容'];

    static $TYPE = [COMPLAINT_USER => '举报用户', COMPLAINT_ROOM => '举报房间', COMPLAINT_MUSIC => '举报音乐'];

    static function createComplaint($complainer, $opts = [])
    {
        $opt_id = fetch($opts, 'opt_id', 0);
        $complaint_type = fetch($opts, 'complaint_type');
        $type = fetch($opts, 'type', '');

        $complaint = new Complaints();
        $complaint->complainer_id = $complainer->id;

        if ($type == COMPLAINT_ROOM) {

            $complaint->room_id = $opt_id;

        } else if ($type == COMPLAINT_USER) {

            $complaint->respondent_id = $opt_id;

        } else if ($type == COMPLAINT_MUSIC) {

            $complaint->music_id = $opt_id;
        }

        $complaint->type = $type;
        $complaint->complaint_type = $complaint_type;
        $complaint->status = AUTH_WAIT;

        $complaint->save();
    }

    static function generateComplaintType($type)
    {
        if ($type == COMPLAINT_ROOM || $type == COMPLAINT_USER) {

            $complaint_type = self::$COMPLAINT_TYPE;
            unset($complaint_type[5]);
            return $complaint_type;

        } else if ($type == COMPLAINT_MUSIC) {

            $complaint_type = self::$COMPLAINT_TYPE;
            unset($complaint_type[2]);
            return $complaint_type;

        }

        return [];
    }
}
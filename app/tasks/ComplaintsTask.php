<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/26
 * Time: 上午11:23
 */
require 'CommonParam.php';

class ComplaintsTask extends \Phalcon\Cli\Task
{
    function fixComplaintsAction()
    {
        $complaints = Complaints::find(
            ['conditions' => 'type is null']
        );
        echoLine('start');
        foreach ($complaints as $complaint) {
            echoLine($complaint->id);
            if ($complaint->room_id) {

                $complaint->type = COMPLAINT_ROOM;

            } else if ($complaint->respondent_id) {

                $complaint->type = COMPLAINT_USER;

            } else if ($complaint->music_id) {

                $complaint->type = COMPLAINT_MUSIC;

            }
            $complaint->save();
        }
    }
}
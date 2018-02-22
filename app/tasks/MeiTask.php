<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午9:49
 */

class MeiTask extends \Phalcon\Cli\Task
{
    function freshRoomUserIdAction()
    {
        $cond = [
            'conditions' => 'user_type = :user_type:',
            'bind' => ['user_type' => USER_TYPE_SILENT]
        ];

        $rooms = Rooms::find($cond);

        $cond1 = [
            'conditions' => 'user_type = :user_type: and avatar_status = :avatar_status: and id >= 100000 and (room_id = 0 or room_id is null)',
            'bind' => ['user_type' => USER_TYPE_SILENT, 'avatar_status' => AUTH_SUCCESS]
        ];

        $users = Users::find($cond1);
        $user_ids = [];

        foreach ($users as $user) {
            $user_ids[] = $user->id;
        }

        if (count($user_ids) > 0) {
            foreach ($rooms as $room) {

                if (mt_rand(1, 100) > 90) {
                    echoLine("continue");
                    continue;
                }

                $index = array_rand($user_ids);
                $user_id = $user_ids[$index];

                $old_user = Users::findFirstById($room->user_id);

                $user = Users::findFirstById($user_id);
                $room->user_id = $user_id;
                $user->room_id = $room->id;

                $room->update();
                $user->update();

                $old_user->room_id = 0;
                $old_user->update();

                $users = $room->findTotalUsers();
                echoLine(count($users));

                foreach ($users as $user1) {
                    $user1->current_room_id = $room->id;
                    $user1->current_room_seat_id = 0;
                    $user1->update();
                }

                unset($user_ids[$index]);
            }
        }
    }

    function getUserIpAction()
    {
        $user = Users::findFirstById(39);
        echoLine($user->getIntranetIp());
        echoLine($user->getOnlineToken());
    }
}
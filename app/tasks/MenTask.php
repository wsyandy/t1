<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/13
 * Time: 下午2:49
 */
class MenTask extends \Phalcon\Cli\Task
{

    function testAction()
    {
        $user = Users::findFirstById(31421);
//        $user->union_id = 0;
//        $user->union_type = 0;
        $user->sid = $user->generateSid('s');
        $user->mobile = 13212345671;
        $user->user_status = USER_STATUS_ON;
        $user->save();
        echoLine($user->id);
//

//        $union = Unions::findFirstById(3);
//        $union_host = Users::findFirstById(31428);
//        $union->refuseJoinUnion($union_host, $user);

//        $db = Users::getUserDb();
//        $db->zrem($union->generateRefusedUsersKey(), $user->id);
//        $db->zrem($union->generateUsersKey(), $user->id);
//        $db->zrem($union->generateNewApplyNumKey(), $user->id);


//        $db->zrem($union->generateCheckUsersKey(), $user->id);
//        $db->zrem($union->generateAllApplyExitUsersKey(), $user->id);
//        $db->zrem($union->generateApplyExitUsersKey(), $user->id);

//        $db->zrem($union->generateCheckUsersKey(), $user->id);
//        $db->zrem($union->generateUsersKey(), $user->id);
//        foreach ([31422, 31423, 31426, 31427, 31428] as $a) {
//            $db->zadd($union->generateUsersKey(), time(), $a);
//        }


    }


}

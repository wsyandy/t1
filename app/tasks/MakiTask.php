<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/7
 * Time: 10:30
 */

class MakiTask extends Phalcon\Cli\Task
{
    public function testxAction()
    {
        $url = 'http://test.momoyuedu.cn/api/backpacks';
        $res = httpGet($url);
        echoLine($res);
    }


    public function dataAction()
    {
        // 查用户id
        $uid = '100201';
        $user = Users::findByConditions(['uid'=>$uid]);
        $user = $user->toJson('users');
        $user_id = $user['users'][0]['id'];
        echoLine($user_id);

        $gifts = [12, 15, 16, 17, 23];
        foreach ($gifts as $i => $value) {
            $backpack = new Backpacks();
            $backpack->user_id = $user_id;
            $backpack->target_id = $value;
            $backpack->number = mt_rand(1, 5);
            $backpack->type = 1;
            $backpack->status = STATUS_ON;
            $backpack->created_at = time();
            $backpack->updated_at = time();
            $backpack->save();
        }
    }
}
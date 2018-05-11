<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 21:06
 */
class MakiTask extends Phalcon\Cli\Task
{
    public function testxAction()
    {
        $line = 0; // 初始值
        $total = 10000; // 流水上线
        $rooms = Rooms::dayStatRooms();
        $rooms = $rooms->toJson('rooms');

        $backpack = new Backpacks();

        foreach ($rooms['rooms'] as $value) {
            $room = Rooms::findFirstById($value['id']);
            $noun = $room->getDayIncome(date('Ymd'));

            echoLine($room);
            echoLine($noun);
            if ($noun >= $line) {
                $backpack->pushClientAboutBoom($total, $noun, $value['id']);
            }
        }
    }


    public function dataAction()
    {
        // 查用户id
        $uid = '100201';
        $user = Users::findByConditions(['uid'=>$uid]);
        $user = $user->toJson('users');
        $user_id = $user['users'][0]['id'];
        echoLine($user_id);

        // 写背包测试数据
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


    public function m1Action()
    {
        $time = strtotime('-3 days', 1524988549);

        $conditions = array(
            'conditions' => 'status = :status: and created_at >= :created_at:',
            'bind' => [
                'status' => STATUS_OFF,
                'created_at' => $time
            ],
            'columns'=>'user_id'
        );
        $order = Orders::find($conditions);

        $count = array(); // 计数器
        foreach ($order as $value) {
            if (isset($count[$value->user_id])) {
                $count[$value->user_id] += 1;
            } else
                $count[$value->user_id] = 1;
        }

        if (empty($count)) return ;
        $count[41792] = 4;

        $content = '尊敬用户：你好！请问您是否在支付的时候遇到了问题？如有疑问请联系官方客服中心400-018-7755解决。';
        $push_data = [
            'title' => '系统充值通知',
            'body' => $content
        ];

        // 次数大于2的user_id
        foreach ($count as $item=>$value) {

            if ($value >= 2) {
                // 需要推送消息的
                Chats::sendSystemMessage($item, CHAT_CONTENT_TYPE_TEXT, $content);

                // 个推
                $user = Users::findFirstById($item);
                Pushers::push($user->getPushContext(), $user->getPushReceiverContext(), $push_data);
            }
        }

    }
}
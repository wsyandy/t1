<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/7
 * Time: 10:30
 */

class MakiTask extends Phalcon\Cli\Task
{
    static private $params;


    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        $name = Phalcon\Text::uncamelize($name);
        list($function) = explode('_', $name);

        if (method_exists($this, $function)) {

            $ref = new ReflectionMethod(__CLASS__, $function);
            if ($ref->isPublic()) {
                self::$params = $arguments[0];
                return $this->$function();
            }
        }
        echoLine($function . ' does not exist');
        return;
    }


    function t1()
    {
        $orders = ['up', 'down', 'enter', 'exit', 'send', 'message', 'kick'];
        $orders = (isset(self::$params[0]) && self::$params[0] == 'list') ? $orders : array_intersect(self::$params, $orders);
        if (empty($orders)) return;


        $room_id = 137039; //136971;137039
        $room = Rooms::findFirstById($room_id);
        if (!$room) return;
        echoLine('进入房间');

        $redis = Users::getHotWriteCache();
        $key = 'pressure_app_user_id_list';
        $entered_users = $redis->zrevrange($key, 0, -1, 'withscores');
        $entered_users = array_keys($entered_users);

        if (in_array('enter', $orders)) {
            $users = Users::findPagination(['order' => 'id desc'], mt_rand(1, 50), 4);
            if (count($users) < 1) return;
            foreach ($users as $user) {
                if ($user->isInAnyRoom()) continue;
                $redis->zadd($key, time(), $user->id);
                Rooms::addWaitEnterSilentRoomList($user->id);
                Rooms::delay()->enterSilentRoom($room->id, $user->id);
            }
        }

        foreach ($entered_users as $user) {
            $user = Users::findFirstById($user);
            foreach ($orders as $order) {
                $order == 'up' && $user->upRoomSeat($user->id, $room->id);
                $order == 'send' && $user->sendGift($user->id, $room->id);
                $order == 'message' && $user->sendTopTopicMessage($user->id, $room->id);
                $order == 'exit' && $room->exitSilentRoom($user);
            }
        }
    }


    function t2()
    {
        $order_list = ['up', 'down', 'enter', 'exit', 'send', 'message', 'kick'];
        $params = array_intersect(self::$params, $order_list);

        $room_id = 136971;
        $room = Rooms::findById($room_id);

        $redis = Users::getHotWriteCache();
        $key = 'pressure_app_user_id_list';
        $users_number = $redis->zcard($key);
        $users = $redis->zrevrange($key, 0, -1, 'withscores');

        if (empty($params)) $params = $order_list;
        $order = count($params) == 1 ? $params[0] : $params[array_rand($params)];

        $real_users = array_rand($users, mt_rand(1, $users_number));
        !is_array($real_users) && $real_users = [$real_users];
        $real_users = Users::findByIds(array_values($real_users));

        foreach ($real_users as $user) {
            $order == 'up' && $user->upRoomSeat($user->id, $room->id);
            $order == 'send' && $user->sendGift($user->id, $room->id);
            $order == 'message' && $user->sendTopTopicMessage($user->id, $room->id);
            $order == 'exit' && $room->exitSilentRoom($user);
        }

    }


    function t3()
    {
        if (isset(self::$params[0])) {
            $params = explode(',', self::$params[0]);
        }
        $orders = empty($params) ? ['enter', 'exit', 'up', 'down', 'message', 'send'] : $params;

        $room_id = 137039;
        $room = Rooms::findById($room_id);

        $room_seat = RoomSeats::findFirst(['conditions' => 'room_id = :room_id: and user_id > 0',
            'bind' => ['room_id' => $room->id]]);

        $conditions = [
            'conditions' => 'user_type = :user_type: and user_status  = :user_status:',
            'bind' => [
                'user_type' => USER_TYPE_SILENT,
                'user_status' => USER_STATUS_ON,
            ],
            'limit' => self::$params[1] ?? 4,
        ];
        $users = Users::find($conditions);

        echoLine(count($users).'-'.count($orders));
        foreach ($users as $user) {
            if ($user->isInAnyRoom()) {
                echoLine('continue');
                continue;
            }

            foreach ($orders as $order) {

                if ($order == 'enter') {

                    Rooms::addWaitEnterSilentRoomList($user->id);
                    Rooms::delay()->enterSilentRoom($room->id, $user->id);

                } elseif ($order == 'exit') {

                    $room->exitSilentRoom($user);

                } elseif ($order == 'up') {

                    $user->upRoomSeat($user->id, $room->id);

                } elseif ($order == 'down') {

                    $user->asyncDownRoomSeat($user->id, $room_seat->id);

                } elseif ($order == 'message') {

                    $user->sendTopTopicMessage($user->id, $room->id);

                } elseif ($order == 'send') {

                    $user->sendGift($user->id, $room->id);
                } else {
                    continue;
                }

            }
        }
    }
}
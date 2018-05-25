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
}
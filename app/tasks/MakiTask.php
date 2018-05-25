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
        $orders = ['up', 'down', 'enter', 'exit', 'send', 'message'];
        $orders = (isset(self::$params[0]) && self::$params[0] == 'list') ? $orders : array_intersect(self::$params, $orders);
        if (empty($orders)) return;


        $room_id = 137039; //136971;
        $room = Rooms::findFirstById($room_id);
        if (!$room) return;
        echoLine('进入房间');

        $users = Users::findPagination(['order' => 'id desc'], mt_rand(1, 50), 4);
        if (count($users) < 1) return;

        $i = 0;
        foreach ($users as $user) {

            if ($user->isInAnyRoom()) {
                continue;
            }

            $i++;
            foreach ($orders as $order) {
                $order == 'up' && $user->upRoomSeat($user->id, $room->id);
                $order == 'send' && $user->sendGift($user->id, $room->id);
                $order == 'message' && $user->sendTopTopicMessage($user->id, $room->id);
                if ($order == 'enter') {
                    Rooms::addWaitEnterSilentRoomList($user->id);
                    Rooms::delay()->enterSilentRoom($room->id, $user->id);
                }

            }
        }
        echoLine($i.'个用户执行');
    }
}
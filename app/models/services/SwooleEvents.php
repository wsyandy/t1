<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/6
 * Time: 下午12:03
 */

namespace services;

class SwooleEvents extends \BaseModel
{
    static $_only_cache = true;

    static function onStartEvent($swoole_service, $server)
    {
        info("------ <services start> ------pid", posix_getpid());
    }

    static function onWorkerStartEvent($swoole_service, $server, $worker_id)
    {
        if ($worker_id < $server->setting['worker_num']) {
            $swoole_service->dispatcher = new \services\RequestDispatcher();
            info("-------- <services onWorkerStart event worker>----worker_id", $worker_id, 'pid', posix_getpid());
        } else {
            info("-------- <services onWorkerStart task worker>----worker_id", $worker_id, 'pid', posix_getpid());
        }
    }

    static function onWorkerStopEvent($swoole_service, $server, $worker_id)
    {
        info("------ <services onWorkerStop> ------worker_id", $worker_id, 'pid', posix_getpid());
    }

    static function onOpenEvent($swoole_service, $server, $request)
    {
        $fd = $request->fd;

        if (!$server->exist($fd)) {
            info($request->fd, "Exce not exist");
            return;
        }

        $server_port = SwooleUtils::getServerPort($server, $fd);

        if ($swoole_service->local_server_port == $server_port) {
            info($fd, "server_to_server onOpen");
            return;
        }

        $online_token = $fd . 'f' . md5(uniqid() . $fd);

        $ip = SwooleUtils::getIntranetIp();
        $sid = SwooleUtils::params($request, 'sid');

        info($sid, $fd, $online_token, $ip);

        $user_id = intval($sid);
        $user = \Users::findFirstById($user_id);

        SwooleUtils::increaseConnectNum(1, $ip);

        if (!$user) {
            $data = ['online_token' => $online_token, 'action' => 'create_token', 'error_code' => ERROR_CODE_FAIL, 'error_reason' => '用户不存在'];
            $server->push($request->fd, json_encode($data, JSON_UNESCAPED_UNICODE));
            return;
        }

        $user->saveFdInfo($fd, $online_token, $ip);

        if ($user->current_room) {
            $user->current_room->bindOnlineToken($user);
        }

        if ($user->current_room_seat) {
            $user->current_room_seat->bindOnlineToken($user);
        }

        $data = ['online_token' => $online_token, 'action' => 'create_token'];
        $server->push($request->fd, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    static function onMessageEvent($swoole_service, $server, $frame)
    {
        $request = new \services\BaseRequest($server, $frame);
        $swoole_service->dispatcher->startAction($swoole_service, $request);
        return;
    }

    static function onCloseEvent($swoole_service, $server, $fd, $from_id)
    {
        $online_token = SwooleUtils::getOnlineTokenByFd($fd);
        $connect_info = $server->connection_info($fd);

        $server_port = fetch($connect_info, 'server_port');

        if ($swoole_service->local_server_port == $server_port) {
            info($fd, "server_to_server onClose");
            return;
        }

        if (!$online_token) {
            info("fd非法", $fd, $from_id);
            return;
        }

        $user_id = SwooleUtils::getUserIdByOnlineToken($online_token);
        $intranet_ip = SwooleUtils::getIntranetIpdByOnlineToken($online_token);
        $user = \Users::findFirstById($user_id);
        SwooleUtils::increaseConnectNum(-1, SwooleUtils::getIntranetIp());

        if ($user) {

            info($user->sid, $fd, "connect close");

            $current_room = \Rooms::findRoomByOnlineToken($online_token);
            $current_room_seat = \RoomSeats::findRoomSeatByOnlineToken($online_token);

            //用户有新的连接 老的连接不推送
            if ($user->online_token == $online_token) {

                if ($intranet_ip) {

                    if ($current_room) {
                        //并发退出房间
                        $exce_exit_room_key = "exce_exit_room_id{$current_room->id}";
                        $exce_exit_room_lock = tryLock($exce_exit_room_key, 1000);
                        $current_room_seat_id = '';

                        if ($current_room_seat) {
                            $current_room_seat_id = $current_room_seat->id;
                            $current_room_seat->down($user);
                        }

                        $current_room->exitRoom($user);
                        $current_room->pushExitRoomMessage($user, $current_room_seat_id);
                        unlock($exce_exit_room_lock);
                    } else {
                        info("room not exists", $user->sid, $online_token);
                    }

                } else {
                    info("intranet_ip is null", $user->sid, $online_token);
                }

            } else {
                info("online_token change", $user->sid, $online_token, $user->online_token);
            }

            //如果有电话进行中
            if ($user->isCalling()) {
                \VoiceCalls::pushHangupInfo($server, $user, $intranet_ip);
            }
        }

        $user->deleteFdInfo($fd, $online_token);
    }

    static function onTaskEvent($swoole_service, $server, $task_id, $from_id, $data)
    {
        info("<services onTask>----task_id", $task_id, 'from_id', $from_id, 'pid', posix_getpid());
    }

    static function onFinishEvent($swoole_service, $server, $task_id, $data)
    {
        info("<services onFinish>----task_id", $task_id, 'pid', posix_getpid());

        if ($data) {
            info("Task 执行成功!");
        } else {
            info("Task 执行失败!");
        }

    }

    static function onRequestEvent($swoole_service, $request, $response)
    {

    }
}
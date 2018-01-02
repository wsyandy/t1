<?php

class Rooms extends BaseModel
{
    static $STATUS = [ROOM_STATUS_OFF => '正常房间', ROOM_STATUS_ON => '被封房间'];

    static function createRoom($user,$name)
    {
        $room = new \Rooms();
        $room->name = $name;
        $room->user_id = $user->id;
        $room->status = ROOM_STATUS_OFF;
        $room->last_at = time();
        $room->save();
        return $room;

    }
}
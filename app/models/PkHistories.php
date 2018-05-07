<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/5/7
 * Time: 下午6:10
 */

class PkHistories extends BaseModel
{
    static $STATUS = [STATUS_ON => '创建成功', STATUS_PROGRESS => 'PK中', STATUS_OFF => 'PK结束'];

    static function createHistory($user, $opts = [])
    {
        $room_id = fetch($opts, 'room_id');
        $player_a_id = fetch($opts, 'player_a_id');
        $player_b_id = fetch($opts, 'player_b_id');
        $pk_type = fetch($opts, 'pk_type');
        $pk_time = fetch($opts, 'pk_time');

        $pk_history = new PkHistories();
        $pk_history->room_id = $room_id;
        $pk_history->user_id = $user->id;
        $pk_history->player_a_id = $player_a_id;
        $pk_history->player_b_id = $player_b_id;
        $pk_history->pk_type = $pk_type;
        $pk_history->expire_at = time() + $pk_time;
        $pk_history->status = STATUS_PROGRESS;

        if ($pk_history->save()) {
            return true;
        }

        return false;
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'winner_id' => $this->winner_id,
            'type_text' => $this->type_text,
            'created_at_text' => $this->created_at_text,
            'player_a_score' => $this->player_a_score,
            'player_b_score' => $this->player_a_score,
        ];
    }
}
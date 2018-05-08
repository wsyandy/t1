<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/5/7
 * Time: 下午6:10
 */

class PkHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_left_pk_user;

    /**
     * @type Users
     */
    private $_right_pk_user;


    static $STATUS = [STATUS_ON => '创建成功', STATUS_PROGRESS => 'PK中', STATUS_OFF => 'PK结束'];

    static function createHistory($user, $opts = [])
    {
        $room_id = fetch($opts, 'room_id');
        $left_pk_user_id = fetch($opts, 'left_pk_user_id');
        $right_pk_user_id = fetch($opts, 'right_pk_user_id');
        $pk_type = fetch($opts, 'pk_type');
        $pk_time = fetch($opts, 'pk_time');

        $pk_history = new PkHistories();
        $pk_history->room_id = $room_id;
        $pk_history->user_id = $user->id;
        $pk_history->left_pk_user_id = $left_pk_user_id;
        $pk_history->left_pk_user_id = $right_pk_user_id;
        $pk_history->pk_type = $pk_type;
        $pk_history->expire_at = time() + $pk_time;
        $pk_history->status = STATUS_PROGRESS;

        if ($pk_history->save()) {
            return $pk_history;
        }

        return null;
    }

    function toSimpleJson()
    {
        $left_pk_user = $this->left_pk_user;
        $right_pk_user = $this->right_pk_user;
        $left_pk_user_score = $this->left_pk_user_score;
        $right_pk_user_score = $this->right_pk_user_score;

        return [
            'id' => $this->id,
            'pk_type' => $this->pk_type,
            'expire_at' => $this->expire_at,
            'created_at' => $this->created_at,
            'created_at_text' => $this->created_at_text,

            'left_pk_user' => [
                'id' => $left_pk_user->id,
                'nickname' => $left_pk_user->nickname,
                'score' => $left_pk_user_score,
                'avatar_small_url' => $left_pk_user->avatar_small_url
            ],

            'right_pk_user' => [
                'id' => $right_pk_user->id,
                'nickname' => $right_pk_user->nickname,
                'score' => $right_pk_user_score,
                'avatar_small_url' => $right_pk_user->avatar_small_url
            ]
        ];
    }
}
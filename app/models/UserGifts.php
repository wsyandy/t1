<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/3
 * Time: 上午10:48
 */

class UserGifts extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type Gifts
     */
    private $_gift;

    static function freshGiftNum($user_id, $gift_id, $gift_num)
    {
        $user_gift = \UserGifts::findFirstOrNew(array('user_id' => $user_id));
        $gift = \Gifts::findById($gift_id);
        $user_gift->gift_id = $gift->id;
        $user_gift->name = $gift->name;
        $user_gift->gift_num = intval($user_gift->gift_num) + $gift_num;
        $user_gift->amount = $gift->amount;
        $user_gift->total_amount = $user_gift->amount * $user_gift->gift_num;
        $user_gift->save();
    }
}
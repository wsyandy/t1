<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午3:21
 */
trait RoomInternational
{
    static function searchRoomsByInternational($opts, $page, $per_page)
    {
        $country_id = fetch($opts, 'country_id');
        $product_channel_id = fetch($opts, 'product_channel_id');
        $uid = fetch($opts, 'uid');
        $name = fetch($opts, 'name');
        $new = fetch($opts, 'new');
        $hot = fetch($opts, 'hot');

        //限制搜索条件
        $cond = [
            'conditions' => 'online_status = ' . STATUS_ON . ' and status = ' . STATUS_ON . ' and product_channel_id = :product_channel_id:',
            'bind' => ['product_channel_id' => $product_channel_id],
            'order' => 'last_at desc, user_type asc'
        ];

        if ($country_id) {
            $cond['conditions'] .= " and country_id = :country_id: ";
            $cond['bind']['country_id'] = $country_id;
        }

        if ($new == STATUS_ON) {
            $cond['conditions'] .= " and new = " . STATUS_ON;
        }

        if ($hot == STATUS_ON) {
            $cond['conditions'] .= " and hot = " . STATUS_ON;
        }

        //用户检索
        if ($uid && $name) {
            $cond['conditions'] .= " and (uid = :uid: or name like :name:) ";
            $cond['bind']['uid'] = $uid;
            $cond['bind']['name'] = "%{$name}%";
        }else {
            if ($name){
                $cond['conditions'] .= " and (name like :name:) ";
                $cond['bind']['name'] = "%{$name}%";
            }
        }

        debug($cond);

        $rooms = Rooms::findPagination($cond, $page, $per_page);

        return $rooms;
    }

}

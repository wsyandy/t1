<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午3:21
 */
trait UserInternational
{
    static function searchByInternational($user, $page, $per_page, $opts = [])
    {
        $user_id = fetch($opts, 'user_id');
        $nickname = fetch($opts, 'nickname');
        $uid = fetch($opts, 'uid');
        $province_id = fetch($opts, 'province_id');
        $city_id = fetch($opts, 'city_id');
        $filter_ids = fetch($opts, 'filter_ids');

        if ($user_id) {
            $cond = ['conditions' => 'id = :user_id:', 'bind' => ['user_id' => $user_id]];
        } else {
            $cond = ['conditions' => 'id <> ' . $user->id];
        }

        //用户检索
        if ($uid && $nickname) {
            $cond['conditions'] .= ' and (uid = :uid: or nickname like :nickname:) ';
            $cond['bind']['uid'] = $uid;
            $cond['bind']['nickname'] = "%{$nickname}%";
        }else {
            if ($nickname){
                $cond['conditions'] .= ' and (nickname like :nickname:) ';
                $cond['bind']['nickname'] = "%{$nickname}%";
            }
        }

        if ($city_id) {
            $cond['conditions'] .= ' and (city_id=:city_id: or geo_city_id=:geo_city_id: or ip_city_id=:ip_city_id:)';
            $cond['bind']['city_id'] = $city_id;
            $cond['bind']['geo_city_id'] = $city_id;
            $cond['bind']['ip_city_id'] = $city_id;
        }

        if ($province_id) {
            $cond['conditions'] .= ' and (province_id=:province_id: or geo_province_id=:geo_province_id: or ip_province_id=:ip_province_id:)';
            $cond['bind']['province_id'] = $province_id;
            $cond['bind']['geo_province_id'] = $province_id;
            $cond['bind']['ip_province_id'] = $province_id;
        }

        $user_type = fetch($opts, 'user_type', USER_TYPE_ACTIVE);
        if ($user_type) {
            $cond['conditions'] .= " and user_type = " . $user_type;
        }

        if ($filter_ids) {
            $cond['conditions'] .= " and id not in ({$filter_ids})";
        }

        $cond['conditions'] .= " and id != " . SYSTEM_ID . " and avatar_status = " . AUTH_SUCCESS . ' and (user_status = ' . USER_STATUS_ON .
            ' or user_status = ' . USER_STATUS_LOGOUT . ')';
        $cond['order'] = 'last_at desc,id desc';

        info($user->id, $cond);

        $users = Users::findPagination($cond, $page, $per_page);

        return $users;
    }
}

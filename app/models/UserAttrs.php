<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午3:21
 */
trait UserAttrs
{

    function toDetailJson()
    {
        return [
            'id' => $this->id,
            'sex' => $this->sex,
            'province_name' => $this->province_name,
            'city_name' => $this->city_name,
            'avatar_url' => $this->avatar_url,
            'avatar_small_url' => $this->avatar_small_url,
            'nickname' => $this->nickname,
            'mobile' => $this->mobile,
            'monologue' => $this->monologue,
            'followed_num' => $this->followed_num,
            'follow_num' => $this->follow_num,
            'friend_num' => $this->friend_num,
            'room_id' => $this->room_id,
            'height' => $this->height,
            'interests' => $this->interests,
            'speaker' => $this->speaker,
            'microphone' => $this->microphone,
            'albums' => $this->albums,
            'user_gifts' => $this->user_gifts,
            'birthday' => $this->birthday_text,
            'age' => $this->age,
            'current_room_id' => $this->current_room_id,
            'current_room_seat_id' => $this->current_room_seat_id,
            'current_channel_name' => $this->current_channel_name,
            'user_role' => $this->user_role,
            'constellation' => $this->constellation_text,
            'im_password' => $this->im_password
        ];
    }

    function toBasicJson()
    {
        return [
            'id' => $this->id,
            'sex' => $this->sex,
            'avatar_url' => $this->avatar_url,
            'avatar_small_url' => $this->avatar_small_url,
            'nickname' => $this->nickname,
            'province_name' => $this->province_name,
            'city_name' => $this->city_name,
            'mobile' => $this->mobile,
            'room_id' => $this->room_id,
            'current_room_id' => $this->current_room_id,
            'current_room_seat_id' => $this->current_room_seat_id,
            'user_role' => $this->user_role,
            'speaker' => $this->speaker,
            'microphone' => $this->microphone,
            'im_password' => $this->im_password
        ];
    }

    function toRelationJson()
    {
        $data = [
            'id' => $this->id,
            'sex' => $this->sex,
            'avatar_url' => $this->avatar_url,
            'avatar_small_url' => $this->avatar_small_url,
            'nickname' => $this->nickname,
            'created_at_text' => $this->created_at_text,
            'room_id' => $this->room_id,
            'current_room_id' => $this->current_room_id,
            'current_room_seat_id' => $this->current_room_seat_id,
            'user_role' => $this->user_role,
            'monologue' => $this->monologue,
            'age' => $this->age
        ];

        if (isset($this->friend_status)) {
            $data['friend_status'] = $this->friend_status;
            $data['friend_status_text'] = $this->friend_status_text;
            $data['self_introduce'] = $this->self_introduce;
        }

        return $data;
    }

    function toSimpleJson()
    {
        $data = [
            'id' => $this->id,
            'sex' => $this->sex,
            'avatar_url' => $this->avatar_url,
            'avatar_small_url' => $this->avatar_small_url,
            'nickname' => $this->nickname,
            'province_name' => $this->province_name,
            'city_name' => $this->city_name,
            'mobile' => $this->mobile,
            'room_id' => $this->room_id,
            'current_room_id' => $this->current_room_id,
            'current_room_seat_id' => $this->current_room_seat_id,
            'current_channel_name' => $this->current_channel_name,
            'current_room_lock' => $this->current_room_lock,
            'user_role' => $this->user_role,
            'monologue' => $this->monologue,
            'distance' => strval(mt_rand(1, 10) / 10) . 'km', //距离 待开发
            'age' => $this->age,
        ];

        return $data;
    }


    public function isWebPlatform()
    {
        if (preg_match('/^(web)$/i', $this->platform)) {
            return true;
        }

        return false;
    }

    public function isTouchPlatform()
    {
        if (preg_match('/^(touch_unknow|touch_ios|touch_android)$/i', $this->platform)) {
            return true;
        }

        return false;
    }

    public function isWxPlatform()
    {
        if (preg_match('/^(weixin_unknow|weixin_ios|weixin_android)$/i', $this->platform)) {
            return true;
        }

        return false;
    }

    public function isClientPlatform()
    {
        if (preg_match('/^(ios|android)$/i', $this->platform)) {
            return true;
        }

        return false;
    }

    function getAvatarUrl()
    {
        if (isBlank($this->avatar)) {
            return '/images/avatar.png';
        }

        return StoreFile::getUrl($this->avatar);
    }

    function getAvatarSmallUrl()
    {
        if (isBlank($this->avatar)) {
            return '/images/avatar.png';
        }

        return StoreFile::getUrl($this->avatar) . '@!small';
    }

    function getMaskedMobile()
    {
        $length = mb_strlen($this->mobile);
        if ($length == 11) {
            return mb_substr($this->mobile, 0, 3) . '*****' . mb_substr($this->mobile, $length - 2, 2);
        }
        return '';
    }

    function albums()
    {
        $albums = Albums::findByUserId($this->id);
        $data = [];
        foreach ($albums as $album) {
            $data[] = $album->toSimpleJson();
        }

        return $data;
    }

    function userGifts()
    {
        return [];
    }

    function getAge()
    {
        $birthday = $this->birthday;

        if (!$birthday) {
            return '';
        }

        $age = date("Y") - date("Y", $birthday);

        return $age;
    }

    function getBirthdayText()
    {
        return date('Y-m-d', $this->birthday);
    }

    function getImPassword()
    {
        return md5($this->id);
    }

    function getCurrentRoomLock()
    {
        if ($this->current_room) {
            return $this->current_room->lock;
        }

        return false;
    }

    function getCurrentChannelName()
    {
        if ($this->current_room) {
            return $this->current_room->channel_name;
        }

        return '';
    }

    function getChannelName()
    {
        if ($this->room) {
            return $this->room->channel_name;
        }

        return '';
    }


    //按照生日计算星座
    function constellationText()
    {
        $c = '';
        $num = date('md', $this->birthday);

        switch ($num) {
            case 321 <= $num && $num <= 420:
                $c = 1;
                break;
            case  421 <= $num && $num <= 520:
                $c = 2;
                break;
            case 521 <= $num && $num <= 621:
                $c = 3;
                break;
            case 622 <= $num && $num <= 722:
                $c = 4;
                break;
            case 723 <= $num && $num <= 823:
                $c = 5;
                break;
            case 824 <= $num && $num <= 923:
                $c = 6;
                break;
            case 924 <= $num && $num <= 1023:
                $c = 7;
                break;
            case 1024 <= $num && $num <= 1122:
                $c = 8;
                break;
            case 1123 <= $num && $num <= 1221:
                $c = 9;
                break;
            case 1222 <= $num && $num <= 1231:
                $c = 10;
                break;
            case 121 <= $num && $num <= 219:
                $c = 11;
                break;
            case 220 <= $num && $num <= 320:
                $c = 12;
                break;
            case 11 <= $num && $num <= 120:
                $c = 10;
                break;
        }

        return Users::$CONSTELLATION[$c];
    }

    function isIos()
    {
        return preg_match('/ios/i', $this->platform);
    }

    function nearby($page, $per_page, $opts = [])
    {

        $latitude = $this->latitude / 10000;
        $longitude = $this->longitude / 10000;

        if (!$latitude || $longitude) {
            $users = \Users::search($this, $page, $per_page);
            return $users;
        }

        $geohash = new GeoHash();
        $hash = $geohash->encode($latitude, $longitude);
        //取前缀，前缀约长范围越小
        $prefix = substr($hash, 0, 6);
        //取出相邻八个区域
        $neighbors = $geohash->neighbors($prefix);
        array_push($neighbors, $prefix);

        $conditions[] = "(platforms like ";
        foreach ($neighbors as $key => $neighbor) {
            $val = $neighbor . '%';
            if ($key) {
                $conditions[] = $val . ' or ';
            } else {
                $conditions[] = $val;
            }
        }

        $conditions[] = ")";

        $conds['conditions'] = implode(' and ', $conditions);

        $users = Users::findPagination($conds, $page, $per_page);
        return $users;
    }

}

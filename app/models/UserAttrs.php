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
            'province_id' => $this->province_id,
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
            'user_role' => $this->user_role
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
            'province_id' => $this->province_id,
            'province_name' => $this->province_name,
            'city_name' => $this->city_name,
            'mobile' => $this->mobile,
            'room_id' => $this->room_id,
            'current_room_id' => $this->current_room_id,
            'current_room_seat_id' => $this->current_room_seat_id,
            'user_role' => $this->user_role,
            'speaker' => $this->speaker,
            'microphone' => $this->microphone
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
            'monologue' => $this->monologue
        ];

        if (isset($this->friend_status)) {
            $data['friend_status'] = $this->friend_status;
            $data['friend_status_text'] = $this->friend_status_text;
        }

        return $data;
    }

    function toSearchJson()
    {
        return [
            'id' => $this->id,
            'sex' => $this->sex,
            'avatar_url' => $this->avatar_url,
            'avatar_small_url' => $this->avatar_small_url,
            'nickname' => $this->nickname,
            'province_id' => $this->province_id,
            'province_name' => $this->province_name,
            'city_name' => $this->city_name,
            'mobile' => $this->mobile,
            'room_id' => $this->room_id,
            'current_room_id' => $this->current_room_id,
            'current_room_seat_id' => $this->current_room_seat_id,
            'monologue' => $this->monologue
        ];
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

    //按照生日计算星座
    function constellationText($is_self = false)
    {
        if (empty($this->birthday)) {
            if (!$is_self) {
                $index = ($this->id % 12) + 1;
                return Users::$CONSTELLATION[$index];
            }
            return '';
        }

        $c = '';
        $num = date('md', strtotime($this->birthday));

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
                break;

        }

        if (321 <= $num && $num <= 420) {
            $c = 1;
        } elseif (421 <= $num && $num <= 520) {
            $c = 2;
        } elseif (521 <= $num && $num <= 621) {
            $c = 3;
        } elseif (622 <= $num && $num <= 722) {
            $c = 4;
        } elseif (723 <= $num && $num <= 823) {
            $c = 5;
        } elseif (824 <= $num && $num <= 923) {
            $c = 6;
        } elseif (924 <= $num && $num <= 1023) {
            $c = 7;
        } elseif (1024 <= $num && $num <= 1122) {
            $c = 8;
        } elseif (1123 <= $num && $num <= 1221) {
            $c = 9;
        } elseif (1222 <= $num && $num <= 1231) {
            $c = 10;
        } elseif (121 <= $num && $num <= 219) {
            $c = 11;
        } elseif (220 <= $num && $num <= 320) {
            $c = 12;
        } elseif (11 <= $num && $num <= 120) {
            $c = 10;
        }

        return Users::$CONSTELLATION[$c];
    }
}

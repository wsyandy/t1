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
            'albums' => $this->albums,
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
            'im_password' => $this->im_password,
            'followed_num' => $this->followed_num,
            'follow_num' => $this->follow_num,
            'current_channel_name' => $this->current_channel_name
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
            'age' => $this->age,
        ];

        if (isset($this->friend_status)) {
            $data['friend_status'] = $this->friend_status;
            $data['friend_status_text'] = $this->friend_status_text;
            $data['self_introduce'] = $this->self_introduce;
        }

        $current_room_lock = false;

        if ($this->current_room_id) {
            $current_room_lock = $this->current_room->lock;
        }

        $data['current_room_lock'] = $current_room_lock;

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

    function toChatJson()
    {
        return array(
            'id' => $this->id,
            'nickname' => $this->nickname,
            'avatar_url' => $this->avatar_small_url
        );
    }

    function toRoomManagerJson()
    {
        $data = [
            'user_id' => $this->id,
            'sex' => $this->sex,
            'avatar_url' => $this->avatar_url,
            'avatar_small_url' => $this->avatar_small_url,
            'nickname' => $this->nickname,
            'is_permanent' => $this->is_permanent, //是否为永久管理员
            'deadline' => $this->deadline //管理员有效期截止时间
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
            return $this->getDefaultAvatar();
        }

        return StoreFile::getUrl($this->avatar);
    }

    function getAvatarSmallUrl()
    {
        if (isBlank($this->avatar)) {
            return $this->getDefaultAvatar();
        }

        return StoreFile::getUrl($this->avatar) . '@!small';
    }

    function getDefaultAvatar()
    {
        $avatar = APP_NAME . '/users/avatar/default_avatar.png';
        return StoreFile::getUrl($avatar);
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
        if (!$this->birthday) {
            return null;
        }
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
        if (!$this->birthday) {
            return $c;
        }
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

    function isAndroid()
    {
        return preg_match('/android/i', $this->platform);
    }

    function generateVoiceChannelKey($channel_name)
    {
        return $this->product_channel->getChannelKey($channel_name, $this->id);
    }

    function getCityName()
    {
        if ($this->city) {
            return $this->city->name;
        }

        return '';
    }

    function getProvinceName()
    {
        if ($this->province) {
            return $this->province->name;
        }

        return '';
    }

    function getOnlineToken()
    {
        $hot_cache = Users::getHotWriteCache();
        $user_online_key = "socket_user_online_user_id" . $this->id;

        return $hot_cache->get($user_online_key);
    }

    //旁听时间
    function getAudienceTimeByDate($date)
    {
        $db = Users::getUserDb();
        $key = Users::generateStatRoomTimeKey('audience', $date);
        $time = $db->zscore($key, $this->id);
        return intval($time / 60);
    }

    //主播时间
    function getBroadcasterTimeByDate($date)
    {
        $db = Users::getUserDb();
        $key = Users::generateStatRoomTimeKey('broadcaster', $date);
        $time = $db->zscore($key, $this->id);
        return intval($time / 60);
    }

    //房主时间
    function getHostBroadcasterTimeByDate($date)
    {
        $db = Users::getUserDb();
        $key = Users::generateStatRoomTimeKey('host_broadcaster', $date);
        $time = $db->zscore($key, $this->id);
        return intval($time / 60);
    }

    function isHuman()
    {
        return USER_TYPE_HUMAN == $this->user_type;
    }

    function changeAvatarAuth($avatar_auth)
    {
        if (isBlank($avatar_auth) ||
            !array_key_exists(intval($avatar_auth), \UserEnumerations::$AVATAR_STATUS)) {
            return;
        }
        $this->avatar_auth = $avatar_auth;
        $this->update();
        if (AUTH_SUCCESS == intval($avatar_auth)) {
            $this->addAuthedList();
        }
    }

    function removeFromWaitAuthList()
    {
        $hot_db = \Users::getHotWriteCache();
        $hot_db->zrem(\Users::waitAuthKey(), $this->id);
    }

    function addAuthedList()
    {
        $hot_db = \Users::getHotWriteCache();
        $hot_db->zadd(\Users::authedKey(), time(), $this->id);
    }
}

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

    function mergeJson()
    {
        return [
            'avatar_small_url' => $this->getAvatarSmallUrl(),
            'avatar_url' => $this->getAvatarUrl(),
            'product_channel_name' => $this->product_channel_name,
            'partner_name' => $this->partner_name,
            'sex_text' => $this->sex_text,
            'geo_province_name' => $this->geo_province_name,
            'geo_city_name' => $this->geo_city_name,
            'ip_province_name' => $this->ip_province_name,
            'ip_city_name' => $this->ip_city_name,
            'province_name' => $this->province_name,
            'city_name' => $this->city_name,
            'user_type_text' => $this->user_type_text,
            'user_status_text' => $this->user_status_text,
            'created_at_text' => $this->created_at_text,
            'register_at_text' => $this->register_at_text,
            'last_at_text' => $this->last_at_text,
            'login_type_text' => $this->login_type_text
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

    function toExportJson()
    {
        return [
            'id' => $this->id,
            'login_name' => $this->login_name,
            'user_type' => $this->user_type,
            'sex' => $this->sex,
            'province_name' => $this->province_name,
            'city_name' => $this->city_name,
            'avatar_url' => $this->avatar_url,
            'nickname' => $this->nickname,
            'mobile' => $this->mobile,
            'height' => $this->height,
            'albums' => $this->albums,
            'birthday' => $this->birthday,
            'platform' => $this->platform,
            'platform_version' => $this->platform_version
        ];
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

    function getWithdrawAmount()
    {
        $hi_coins = $this->hi_coins;
        if (!$hi_coins) {
            return 0;
        } else {
            return $hi_coins / 10;
        }
    }

    public function lastLoginAt()
    {
        if (!$this->last_at) {
            return $this->created_at;
        }

        return $this->last_at;
    }

    function getOnlineToken()
    {
        $hot_cache = Users::getHotWriteCache();
        $user_online_key = "socket_user_online_user_id" . $this->id;
        return $hot_cache->get($user_online_key);
    }

    //用户长连接对应的ip
    function getIntranetIp()
    {
        $hot_cache = Users::getHotReadCache();
        $online_token = $this->getOnlineToken();

        if (!$online_token) {
            return '';
        }

        $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $online_token;
        $intranet_ip = $hot_cache->get($fd_intranet_ip_key);

        return $intranet_ip;
    }

    //用户长连接对应的fd
    function getUserFd()
    {
        $hot_cache = Users::getHotReadCache();
        $online_token = $this->getOnlineToken();

        if (!$online_token) {
            return '';
        }

        $fd_key = "socket_push_fd_" . $online_token;
        $fd = $hot_cache->get($fd_key);

        return $fd;
    }

    //等级文案
    function getLevelText()
    {
        $levels = [1, 6, 11, 16, 21, 26, 31, 36];
        $level_texts = ['青铜', '白银', '黄金', '铂金', '钻石', '王者', '星耀'];
        $user_level = $this->level;

        if ($user_level < 1) {
            return '';
        } elseif ($user_level >= 35) {
            return '星耀5';
        }

        $level_text = '';

        foreach ($levels as $index => $level) {

            if (isset($levels[$index + 1]) && $user_level >= $level && $user_level < $levels[$index + 1]) {
                $level_text = $level_texts[$index] . ($user_level - $index * 5);
            }
        }

        return $level_text;
    }
}

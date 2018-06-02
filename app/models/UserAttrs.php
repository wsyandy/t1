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
        $data = [
            'id' => $this->id,
            'uid' => $this->uid,
            'sex' => $this->sex,
            'province_name' => $this->province_name,
            'city_name' => $this->city_name,
            'avatar_url' => $this->avatar_url,
            'avatar_big_url' => $this->avatar_big_url,
            'avatar_small_url' => $this->avatar_small_url,
            'avatar_100x100_url' => $this->avatar_100x100_url,
            'avatar_60x60_url' => $this->avatar_60x60_url,
            'nickname' => $this->nickname,
            'mobile' => $this->mobile,
            'monologue' => $this->monologue_text,
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
            'im_password' => $this->im_password,
            'level' => $this->level,
            'segment' => $this->segment,
            'segment_text' => $this->segment_text,
            'next_level_experience' => $this->next_level_experience,
            'id_card_auth' => $this->id_card_auth,
            'diamond' => $this->diamond,
            'experience' => intval($this->experience),
            'medal_image_url' => $this->medal_image_url
        ];

        if (isPresent($this->union)) {
            $data['union_name'] = $this->union->name;
        } else {
            $data['union_name'] = '';
        }

        return $data;
    }

    function mergeJson()
    {
        return [
            'avatar_big_url' => $this->avatar_big_url,
            'avatar_small_url' => $this->avatar_small_url,
            'avatar_100x100_url' => $this->avatar_100x100_url,
            'avatar_60x60_url' => $this->avatar_60x60_url,
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
            'login_type_text' => $this->login_type_text,
            'level' => $this->level,
            'segment' => $this->segment,
            'segment_text' => $this->segment_text
        ];
    }

    function toBasicJson()
    {
        $data = [
            'id' => $this->id,
            'uid' => $this->uid,
            'sex' => $this->sex,
            'birthday' => $this->birthday_text,
            'avatar_url' => $this->avatar_url,
            'avatar_big_url' => $this->avatar_big_url,
            'avatar_small_url' => $this->avatar_small_url,
            'avatar_100x100_url' => $this->avatar_100x100_url,
            'avatar_60x60_url' => $this->avatar_60x60_url,
            'nickname' => $this->nickname,
            'mobile' => $this->mobile,
            'room_id' => $this->room_id,
            'current_room_id' => $this->current_room_id,
            'current_room_seat_id' => $this->current_room_seat_id,
            'current_channel_name' => $this->current_channel_name,
            'user_role' => $this->user_role,
            'speaker' => $this->speaker,
            'im_password' => $this->im_password,
            'level' => $this->level,
            'segment' => $this->segment,
            'segment_text' => $this->segment_text,
            'gold' => $this->gold,
            'diamond' => $this->diamond,
            'medal_image_url' => $this->medal_image_url,
            'province_name' => $this->province_name,
            'city_name' => $this->city_name,
            'followed_num' => $this->followed_num,
            'follow_num' => $this->follow_num
        ];

        return $data;
    }

    function toRelationJson()
    {
        $data = [
            'id' => $this->id,
            'sex' => $this->sex,
            'avatar_url' => $this->avatar_url,
            'avatar_big_url' => $this->avatar_big_url,
            'avatar_small_url' => $this->avatar_small_url,
            'avatar_100x100_url' => $this->avatar_100x100_url,
            'avatar_60x60_url' => $this->avatar_60x60_url,
            'nickname' => $this->nickname,
            'created_at_text' => $this->created_at_text,
            'room_id' => $this->room_id,
            'current_room_id' => $this->current_room_id,
            'current_room_seat_id' => $this->current_room_seat_id,
            'user_role' => $this->user_role,
            'monologue' => $this->monologue_text,
            'age' => $this->age,
            'level' => $this->level,
            'segment' => $this->segment,
            'segment_text' => $this->segment_text,
            'followed' => $this->followed,
            'medal_image_url' => $this->medal_image_url
        ];

        if (isset($this->friend_status)) {
            $data['friend_status'] = $this->friend_status;
            $data['friend_status_text'] = $this->friend_status_text;
            $data['self_introduce'] = $this->self_introduce;
        }

        if (isset($this->friend_note)) {
            $data['friend_note'] = $this->friend_note;
        }

        $data['current_room_lock'] = $this->current_room_lock;

        return $data;
    }

    function toSimpleJson()
    {
        $data = [
            'id' => $this->id,
            'uid' => $this->uid,
            'sex' => $this->sex,
            'avatar_url' => $this->avatar_url,
            'avatar_big_url' => $this->avatar_big_url,
            'avatar_small_url' => $this->avatar_small_url,
            'avatar_100x100_url' => $this->avatar_100x100_url,
            'avatar_60x60_url' => $this->avatar_60x60_url,
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
            'monologue' => $this->monologue_text,
            'distance' => $this->distance,
            'age' => $this->age,
            'level' => $this->level,
            'segment' => $this->segment,
            'segment_text' => $this->segment_text,
            'medal_image_url' => $this->medal_image_url,
            'followed_num' => $this->followed_num,
            'has_red_packet' => $this->has_red_packet
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
            'avatar_url' => $this->avatar_small_url,
            'sex' => $this->sex
        );
    }

    function toCpJson()
    {
        $datas = [
            'id' => $this->id,
            'nickname' => $this->nickname,
            'avatar_url' => $this->avatar_small_url,
            'uid' => $this->uid
        ];
        if (isset($this->cp_value)) {
            $datas['cp_value'] = $this->cp_value;
        }
        if (isset($this->avatar_base64_url)) {
            $datas['avatar_base64_url'] = $this->avatar_base64_url;
        }
        return $datas;

    }

    function toRoomManagerJson()
    {
        $data = [
            'user_id' => $this->id,
            'uid' => $this->uid,
            'sex' => $this->sex,
            'avatar_url' => $this->avatar_url,
            'avatar_big_url' => $this->avatar_big_url,
            'avatar_small_url' => $this->avatar_small_url,
            'avatar_100x100_url' => $this->avatar_100x100_url,
            'avatar_60x60_url' => $this->avatar_60x60_url,
            'nickname' => $this->nickname,
            'is_permanent' => $this->is_permanent, //是否为永久管理员
            'deadline' => $this->deadline //管理员有效期截止时间,
        ];

        return $data;
    }

    //待优化 数据存缓存
    function toRoomManagerSimpleJson()
    {
        $data = [
            'user_id' => $this->id,
            'is_permanent' => $this->is_permanent, //是否为永久管理员
            'deadline' => $this->deadline //管理员有效期截止时间,
        ];

        return $data;
    }

    function toUnionJson()
    {
        $data = [
            'id' => $this->id,
            'uid' => $this->uid,
            'nickname' => $this->nickname,
            'age' => $this->age,
            'sex' => $this->sex,
            'avatar_url' => $this->avatar_url,
            'avatar_big_url' => $this->avatar_big_url,
            'avatar_small_url' => $this->avatar_small_url,
            'avatar_100x100_url' => $this->avatar_100x100_url,
            'avatar_60x60_url' => $this->avatar_60x60_url,
            'union_charm_value' => $this->union_charm_value,
            'union_wealth_value' => $this->union_wealth_value,
            'monologue' => $this->monologue_text,
            'current_room_id' => $this->current_room_id,
            'is_exit_union' => $this->is_exit_union,
            'hi_coins' => $this->hi_coins
        ];

        if (isset($this->apply_status)) {
            $data['apply_status'] = $this->apply_status;
            $data['apply_status_text'] = $this->apply_status_text;
        }

        if (isset($this->charm)) {
            $data['charm_value'] = valueToStr($this->charm);
        }

        if (isset($this->wealth)) {
            $data['wealth_value'] = valueToStr($this->wealth);
        }

        if ($data['age'] === 0) {
            $data['age'] = '';
        }

        return $data;
    }

    function toRecommendJson()
    {
        $data = [
            'id' => $this->id,
            'nickname' => $this->nickname,
            'age' => $this->age,
            'sex' => $this->sex,
            'avatar_url' => $this->avatar_url,
            'avatar_big_url' => $this->avatar_big_url,
            'avatar_small_url' => $this->avatar_small_url,
            'avatar_100x100_url' => $this->avatar_100x100_url,
            'avatar_60x60_url' => $this->avatar_60x60_url,
            'monologue' => $this->monologue_text,
            'current_room_id' => $this->current_room_id,
            'tags' => $this->tags,
            'recommend_tip' => $this->recommend_tip
        ];

        return $data;
    }

    function toUnionStatJson()
    {
        $json = [
            'income' => $this->income,
            'host_broadcaster_time_text' => $this->getHostBroadcasterTimeText(),
            'broadcaster_time_text' => $this->getBroadcasterTimeText(),
            'audience_time_text' => $this->getAudienceTimeText(),
        ];

        return array_merge($this->toBasicJson(), $json);
    }

    function toRankListJson()
    {
        $data = [
            'id' => $this->id,
            'nickname' => $this->nickname,
            'age' => $this->age,
            'sex' => $this->sex,
            'avatar_url' => $this->avatar_url,
            'avatar_big_url' => $this->avatar_big_url,
            'avatar_small_url' => $this->avatar_small_url,
            'avatar_100x100_url' => $this->avatar_100x100_url,
            'avatar_60x60_url' => $this->avatar_60x60_url,
            'rank' => $this->rank,
            'level' => $this->level,
            'segment' => $this->segment,
            'segment_text' => $this->segment_text,
            'medal_image_url' => $this->medal_image_url
        ];

        if (isset($this->contributing_hi_conins)) {
            $data['hi_coin'] = valueToStr($this->contributing_hi_conins);
        }

        if (isset($this->charm)) {
            $data['charm_value'] = valueToStr($this->charm);
            $data['charm_value_text'] = $this->charm_value_text;
        }

        if (isset($this->wealth)) {
            $data['wealth_value'] = valueToStr($this->wealth);
            $data['wealth_value_text'] = $this->wealth_value_text;
        }

        return $data;
    }

    // es json
//    function toSearchJson()
//    {
//        $data = ['id' => $this->id, 'uid' => $this->uid,
//            'nickname' => $this->nickname, 'province_id' => $this->province_id, 'city_id' => $this->city_id,
//            'ip_province_id' => $this->ip_province_id, 'ip_city_id' => $this->ip_city_id,
//            'geo_province_id' => $this->geo_province_id, 'geo_city_id' => $this->geo_city_id,
//            'user_type' => $this->user_type, 'avatar_status' => $this->avatar_status, 'user_status' => $this->user_status,
//            'created_at' => $this->created_at, 'register_at' => $this->register_at, 'last_at' => $this->last_at,
//            'third_name' => $this->third_name, 'login_type' => $this->login_type, 'mobile' => $this->mobile, 'login_name' => $this->login_name,
//            'platform' => $this->platform, 'product_channel_id' => $this->product_channel_id, 'partner_id' => $this->partner_id,
//            'sex' => $this->sex, 'ip' => $this->ip, 'device_id' => $this->device_id, 'room_id' => $this->room_id,
//            'user_role' => $this->user_role, 'current_room_id' => $this->current_room_id, 'geo_hash' => $this->geo_hash, 'level' => $this->level,
//            'union_id' => $this->union_id, 'id_card_auth' => $this->id_card_auth, 'organisation' => $this->organisation, 'share_parent_id' => $this->share_parent_id,
//        ];
//
//        if ($this->geo_hash) {
//            $data['geo_hash_7'] = substr($this->geo_hash, 0, 7);
//            $data['geo_hash_6'] = substr($this->geo_hash, 0, 6);
//            $data['geo_hash_5'] = substr($this->geo_hash, 0, 5);
//            $data['geo_hash_4'] = substr($this->geo_hash, 0, 4);
//        }
//
//        return $data;
//    }

    function isSilent()
    {
        return USER_TYPE_SILENT == $this->user_type;
    }

    function isActive()
    {
        return USER_TYPE_ACTIVE == $this->user_type;
    }

    function isBlocked()
    {
        return USER_STATUS_BLOCKED_ACCOUNT == $this->user_status
            || USER_STATUS_BLOCKED_DEVICE == $this->user_status || USER_STATUS_OFF == $this->user_status;
    }

    function isNormal()
    {
        if ($this->isWxPlatform() || $this->isTouchPlatform()) {
            return USER_STATUS_ON === $this->user_status || USER_STATUS_LOGOUT == $this->user_status;
        }

        return USER_STATUS_ON === $this->user_status;
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

    function getAvatar60x60Url()
    {
        if (isBlank($this->avatar)) {
            return $this->getDefaultAvatar();
        }

        return StoreFile::getUrl($this->avatar) . '@!60x60';
    }

    function getAvatar100x100Url()
    {
        if (isBlank($this->avatar)) {
            return $this->getDefaultAvatar();
        }

        return StoreFile::getUrl($this->avatar) . '@!100x100';
    }


    function getAvatarSmallUrl()
    {
        if (isBlank($this->avatar)) {
            return $this->getDefaultAvatar();
        }

        return StoreFile::getUrl($this->avatar) . '@!small';
    }

    function getAvatarBigUrl()
    {
        if (isBlank($this->avatar)) {
            return $this->getDefaultAvatar();
        }

        return StoreFile::getUrl($this->avatar) . '@!big';
    }

    function getDefaultAvatar()
    {
        $avatar = APP_NAME . '/users/avatar/default_avatar' . $this->sex . '.png';
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
        $cond = [
            'conditions' => "user_id = :user_id: and auth_status != :auth_status:",
            'bind' => ['user_id' => $this->id, 'auth_status' => AUTH_FAIL],
            'order' => 'id desc'
        ];

        $albums = Albums::find($cond);
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
            return 0;
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
        if ($this->current_room_id) {
            $room_db = Rooms::getRoomDb();
            return $room_db->hget("room_" . $this->current_room_id, 'lock');
        }

        return false;
    }

    function getCurrentChannelName()
    {
        if ($this->current_room_id) {
            return $this->current_room_channel_name;
        }

        return '';
    }

//    function getChannelName()
//    {
//        if ($this->room) {
//            return $this->room->channel_name;
//        }
//
//        return '';
//    }

    function getMonologueText()
    {
        if (isBlank($this->monologue)) {
            return '';
        }

        return $this->monologue;
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
        return preg_match('/^ios/i', $this->platform);
    }

    function isAndroid()
    {
        return preg_match('/^android/i', $this->platform);
    }

    function getChannelKey($channel_name)
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
        return intval($time);
    }

    //主播时间
    function getBroadcasterTimeByDate($date)
    {
        $db = Users::getUserDb();
        $key = Users::generateStatRoomTimeKey('broadcaster', $date);
        $time = $db->zscore($key, $this->id);
        return intval($time);
    }

    //房主时间
    function getHostBroadcasterTimeByDate($date)
    {
        $db = Users::getUserDb();
        $key = Users::generateStatRoomTimeKey('host_broadcaster', $date);
        $time = $db->zscore($key, $this->id);
        return intval($time);
    }

    //旁听时间
    function getAudienceTimeText()
    {
        return secondsToText($this->audience_time);
    }

    //主播时间
    function getBroadcasterTimeText()
    {
        return secondsToText($this->broadcaster_time);
    }

    //房主时间
    function getHostBroadcasterTimeText()
    {
        return secondsToText($this->host_broadcaster_time);
    }

    function getWithdrawAmount()
    {
        $hi_coins = $this->hi_coins;

        if (!$hi_coins) {
            return 0;
        } else {
            $rate = \HiCoinHistories::rateOfHiCoinToCny();
            $hi_coins = $hi_coins / $rate;

            return intval($hi_coins * 100) / 100;
        }
    }

    function getHiCoinText()
    {
        $hi_coins = $this->hi_coins;

        if (!$hi_coins) {
            return 0;
        } else {
            return intval($hi_coins * 100) / 100;
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

    function getReceiveGiftNum()
    {
        $num = UserGifts::sum(['conditions' => 'user_id = :user_id: and gift_type = :gift_type:',
            'bind' => ['user_id' => $this->id, 'gift_type' => GIFT_TYPE_COMMON], 'column' => 'num']);

        return $num;
    }

    function getApplyStatusText()
    {
        if ($this->apply_status == 1) {
            return "已同意";
        }

        if ($this->apply_status == -1) {
            return "已拒绝";
        }

        return "同意";
    }

    function getDaysIncome($start_at, $end_at)
    {
        $total_amount = GiftOrders::sum(['conditions' => 'user_id = :user_id: and created_at >= :start_at: and created_at <= :end_at:',
            'bind' => ['user_id' => $this->id, 'start_at' => $start_at, 'end_at' => $end_at], 'column' => 'amount']);
        return intval($total_amount);
    }

    function getNextLevelExperience()
    {
        $level = $this->level;

        $level_ranges = [0, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000,
            10000, 11000, 16000, 21000, 26000, 31000, 36000, 56000, 76000, 96000, 116000, 136000, 186000, 236000, 286000,
            336000, 386000];

        if (isDevelopmentEnv() || time() >= beginOfDay(strtotime('2018-06-08'))) {
            $level_ranges = [0, 120, 246, 378, 516, 660, 810, 966, 1128, 1296, 1470, 3000, 4590, 6240, 7950, 9720, 11550,
                13440, 15390, 17400, 19470, 28800, 38430, 48360, 58590, 69120, 109200, 150480, 192960, 236640, 281520,
                390600, 502680, 617760, 735840, 856920];
        }

        if ($level >= count($level_ranges) - 1) {
            return 0;
        }

        $next_level_experience = $level_ranges[$level + 1];

        return $next_level_experience;
    }

    //用户的座驾
    function getUserCarGift()
    {
        $exist_user_gift = \UserGifts::findFirst(
            ['conditions' => 'user_id = :user_id: and gift_type = :gift_type: and status = :status: and expire_at > :expire_at:',
                'bind' => ['user_id' => $this->id, 'gift_type' => GIFT_TYPE_CAR, 'status' => STATUS_ON, 'expire_at' => time()],
                'order' => 'id desc'
            ]);

        return $exist_user_gift;
    }

    //分成比例
    function rateOfDiamondToHiCoin()
    {
        if ($this->isIdCardAuth()) {

            $hour = intval(date("H"));

            if ($hour >= 0 && $hour <= 7) {
                return 6 / 100;
            }

            return 5 / 100;
        }

        return 4.5 / 100;
    }

    function getTags()
    {
        $tag_man_1 = ['color' => '#A0CDFF', 'text' => '正太'];
        $tag_man_2 = ['color' => '#83A5FF', 'text' => '95后小哥哥'];
        $tag_man_3 = ['color' => '#6197FF', 'text' => '小哥哥'];

        $tag_woman_1 = ['color' => '#FE9BDF', 'text' => '萝莉'];
        $tag_woman_2 = ['color' => '#FF8BB6', 'text' => '95后小姐姐'];
        $tag_woman_3 = ['color' => '#FF8694', 'text' => '小姐姐'];

        $tag_active = ['color' => '##FFAD36', 'text' => '活跃'];

        $tag_money_1 = ['color' => '#AD7DFA', 'text' => '土豪'];
        $tag_money_2 = ['color' => '#AD7DFA', 'text' => '潜力股'];


        $tag_woman_money = ['color' => '#F86BD4', 'text' => '白富美'];
        $tag_man_money = ['color' => '#83A5FF', 'text' => '高富帅'];

        $birth_year = date('Y', $this->birthday);

        $tags = [];

        if ($this->sex) {

            if ($birth_year >= 2000) {
                $tags['man'] = $tag_man_1;
            } else if ($birth_year >= 1995) {
                $tags['man'] = $tag_man_2;
            } else {
                $tags['man'] = $tag_man_3;
            }
        } else {
            if ($birth_year >= 2000) {
                $tags['woman'] = $tag_woman_1;
            } else if ($birth_year >= 1995) {
                $tags['woman'] = $tag_woman_2;
            } else {
                $tags['woman'] = $tag_woman_3;
            }
        }

        if ($this->wealth_value > 10000) {
            $tags['money'] = $tag_money_1;
        } else if ($this->wealth_value > 3000) {
            $tags['money'] = $tag_money_2;
        }

        if ($this->charm_value > 10000) {
            if (array_key_exists('money', $tags)) {
                unset($tags['money']);
                if (array_key_exists('woman', $tags)) {
                    unset($tags['woman']);
                    $tags['woman_money'] = $tag_woman_money;
                } else if (array_key_exists('man', $tags)) {
                    unset($tags['man']);
                    $tags['man_money'] = $tag_man_money;
                }
            }
        }

        if (count($tags) < 2) {
            $tags[] = $tag_active;
        }

        return array_values($tags);
    }

    //活动剩余抽奖次数
    function getLuckyDrawNum($activity_id)
    {
        $db = Users::getUserDb();
        $key = 'lucky_draw_num_activity_id_' . $activity_id; //记录每个用户可以抽多少次
        return intval($db->zscore($key, $this->id));
    }

    function isMobileLogin()
    {
        return $this->login_type == USER_LOGIN_TYPE_MOBILE;
    }

    function isThirdLogin()
    {
        return in_array($this->login_type, [USER_LOGIN_TYPE_WEIXIN, USER_LOGIN_TYPE_QQ, USER_LOGIN_TYPE_SINAWEIBO]);
    }

    function isEmailLogin()
    {
        return $this->login_type == USER_LOGIN_TYPE_EMAIL;
    }

    function bindMobileStatus()
    {
        if ($this->mobile) {
            return 1;
        }

        return 2;
    }

    static function getRatio($tonic_ratio)
    {
        $all_ratio = 100;
        $consonant_ratio1 = mt_rand(20, 29);
        list($consonant_ratio2, $consonant_ratio3) = self::getResidueRatio($all_ratio, $tonic_ratio, $consonant_ratio1);

        if (isBlank($consonant_ratio2) || isBlank($consonant_ratio3)) {
            list($consonant_ratio2, $consonant_ratio3) = self::getResidueRatio($all_ratio, $tonic_ratio, $consonant_ratio1);
        }
        return [$consonant_ratio1, $consonant_ratio2, $consonant_ratio3];


    }

    static function getResidueRatio($all_ratio, $tonic_ratio, $consonant_ratio1)
    {
        $consonant_ratio2 = mt_rand(10, $all_ratio - $tonic_ratio - $consonant_ratio1);
        if ($consonant_ratio2 > 20) {
            self::getResidueRatio($all_ratio, $tonic_ratio, $consonant_ratio1);
        } else {
            $consonant_ratio3 = $all_ratio - $tonic_ratio - $consonant_ratio1 - $consonant_ratio2;
            if ($consonant_ratio3 == 0) {
                self::getResidueRatio($all_ratio, $tonic_ratio, $consonant_ratio1);
            } else {
                return [$consonant_ratio2, $consonant_ratio3];
            }
        }
    }

    static function getTonicAvatar($tonic)
    {
        switch ($tonic) {
            case '少女音':
                $avatar_url = '/m/images/shaonv.png';
                break;
            case '萝莉音':
                $avatar_url = '/m/images/luoli.png';
                break;
            case '少萝音':
                $avatar_url = '/m/images/shaoluo.png';
                break;
            case '少御音':
                $avatar_url = '/m/images/shaoyu.png';
                break;
            case '御姐音':
                $avatar_url = '/m/images/yujie.png';
                break;
            case '青年音':
                $avatar_url = '/m/images/qingnian.png';
                break;
            case '正太音':
                $avatar_url = '/m/images/zhengtai.png';
                break;
            case '少年音':
                $avatar_url = '/m/images/shaonian.png';
                break;
            case '暖男音':
                $avatar_url = '/m/images/nuannan.png';
                break;
            case '青受音':
                $avatar_url = '/m/images/qingshou.png';
                break;
            default:
                $avatar_url = '';
                break;
        }
        return $avatar_url;
    }

    static function getImageForShare($image_data)
    {
        $image_data = trim($image_data);

        //data:image/octet-stream;base64
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $image_data, $result)) {
            $type = $result[2];
            if (in_array($type, array('pjpeg', 'jpeg', 'jpg', 'gif', 'bmp', 'png'))) {
                $file_name = 'voice_identify_' . md5(uniqid(mt_rand())) . '.jpg';
                $new_file = $source_filename = APP_ROOT . 'temp/' . $file_name;
                if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $image_data)))) {
                    $img_path = str_replace('../../..', '', $new_file);
                    $res = \StoreFile::upload($img_path, APP_NAME . '/users/voices/' . $file_name);
                    $image_url = \StoreFile::getUrl($res);
                    if (file_exists($source_filename)) {
                        unlink($source_filename);
                    }
                    if ($image_url) {
                        return $image_url;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                //文件类型错误
                return false;
            }

        } else {
            //文件错误
            return false;
        }
    }

    //获取屏蔽的房间id
    function getShieldRoomIds()
    {
        $total_room_ids = [];

        $province_id = $this->province_id;
        $city_id = $this->city_id;
        $ip_province_id = $this->ip_province_id;
        $ip_city_id = $this->ip_city_id;
        $geo_province_id = $this->geo_province_id;
        $geo_city_id = $this->geo_city_id;

        $province_ids = [$province_id, $ip_province_id, $geo_province_id];
        $city_ids = [$city_id, $ip_city_id, $geo_city_id];

        $province_ids = array_unique(array_filter($province_ids));
        $city_ids = array_unique(array_filter($city_ids));

        $hot_cache = self::getHotReadCache();

        foreach ($province_ids as $province_id) {
            $room_ids = $hot_cache->zrange('room_shield_province_id_' . $province_id, 0, -1);

            if ($room_ids) {
                $total_room_ids = array_merge($total_room_ids, $room_ids);
            }
        }

        foreach ($city_ids as $city_id) {
            $room_ids = $hot_cache->zrange('room_shield_city_id_' . $city_id, 0, -1);

            if ($room_ids) {
                $total_room_ids = array_merge($total_room_ids, $room_ids);
            }
        }

        $total_room_ids = array_unique(array_filter($total_room_ids));

        return $total_room_ids;
    }

    function getDayCharmValue()
    {
        $user_db = Users::getUserDb();
        $key = Users::generateFieldRankListKey('day', 'charm');

        return intval($user_db->zscore($key, $this->id));
    }

    function getDayWealthValue()
    {
        $user_db = Users::getUserDb();
        $key = Users::generateFieldRankListKey('day', 'wealth');

        return intval($user_db->zscore($key, $this->id));
    }

    function getMedalImageUrl()
    {
        if (isProduction()) {
            return '';
        }

        return "http://test.momoyuedu.cn/m/images/level_1.png";
    }

    function getCharmValueText()
    {
        return "魅力: " . valueToStr($this->charm);
    }

    function getWealthValueText()
    {
        return "财富: " . valueToStr($this->wealth);
    }

    function getCurrentWeekActivityInfo($key)
    {
        $db = \Users::getUserDb();
        $current_rank = $this->getRankByKey($key);
        $current_user_info['current_rank'] = $current_rank <= 100 ? $current_rank : $current_rank + 1000;
        $current_user_info['current_rank_text'] = $current_rank <= 100 ? $current_rank : '100+';
        $current_score = $db->zscore($key, $this->id);
        $current_user_info['current_score'] = $current_score ? $current_score : 0;

        return $current_user_info;
    }


    function getCurrentRankListCpInfo($type = 'week', $opts = [])
    {
        $db = \Users::getUserDb();
        //先去当前用户的周情侣值最高的数据，拼接成员，到周总榜，拿到当前排名和当前分数
        $total_cp_week_charm_key = \Users::generateFieldRankListKey($type, 'cp', $opts);

        //当前用户周情侣值
        $cp_week_charm_key = \Couples::generateCoupleKeyForUser($type, $this->id, $opts);
        $height_data = $db->zrevrange($cp_week_charm_key, 0, 0);
        info($height_data);
        if (!$height_data) {
            $seraglio_key = \Couples::generateSeraglioKey($this->id);
            $num = $db->zcard($seraglio_key);
            info('个数', $num);
            if (!$num) {
                return '';
            }

            $other_user_id = $db->zrange($seraglio_key, 0, 0)[0];
        } else {
            $other_user_id = $height_data[0];
        }

        $key = \Couples::generateSeraglioKey($this->id);
        $status = $db->zscore($key, $other_user_id);
        switch ($status) {
            case 1:
                $member = $other_user_id . '_' . $this->id;
                break;
            case 2:
                $member = $this->id . '_' . $other_user_id;
                break;
        }

        $rank = $db->zrrank($total_cp_week_charm_key, $member);
        $current_score = $db->zscore($total_cp_week_charm_key, $member);

        if (is_null($rank)) {
            $total_entries = $db->zcard($total_cp_week_charm_key);
            if ($total_entries) {
                $rank = $total_entries;
            }
        }

        $current_rank = $rank + 1;

        $current_user_info['current_rank'] = $current_rank;
        $current_user_info['current_rank_text'] = $current_rank <= 100 ? $current_rank : '100+';
        $current_user_info['current_score'] = $current_score ? $current_score : 0;

        $other_user = \Users::findFirstById($other_user_id);
        $current_user_info['other_user_nickname'] = $other_user->nickname;
        $current_user_info['other_user_avatar_url'] = $other_user->avatar_url;

        return $current_user_info;
    }
}

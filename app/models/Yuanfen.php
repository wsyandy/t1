<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 03/02/2018
 * Time: 16:20
 * 导入缘分用户
 */
class Yuanfen
{
    private $filename;
    private $from_dev = false; // 从交友测试机导用户
    private $silent_num = 0;

    static $SILENT_NUM_LIMIT = 50000;

    function __construct($filename, $from_dev = false)
    {
        $this->filename = $filename;
        $this->from_dev = $from_dev;
        $this->silent_num = $this->silentUserNum();
    }

    function silentUserNum()
    {
        $search_db = \Users::getUserDb();
        return intval($search_db->get($this->silentKey()));
    }

    function silentKey()
    {
        return "silent_users_num";
    }

    function updateSilentUserNum()
    {
        $search_db = \Users::getUserDb();
        $total = $search_db->incr($this->silentKey());
        $this->silent_num = $total;
    }

    function parseFile()
    {
        $f = fopen($this->filename, 'r');
        while (true) {
            $line = fgets($f);
            if ($this->createUser($line)) {
                $this->updateSilentUserNum();
                if ($this->isFinished()) {
                    break;
                }
            }
        }
        fclose($f);
    }

    function createUser($line)
    {
        $platforms = ['ios', 'android'];
        $data = explode('|', $line);
        $yuanfen_id = $data[0];
        $platform = $data[1];
        $sex = $data[2];
        $nickname = $data[3];
        $avatar_path = $data[4];
        $province_name = $data[6];
        $city_name = $data[7];
        $ip = $data[8];
        $platform_version = $data[10];
        $latitude = $data[11];
        $longitude = $data[12];
        $height = $data[13];
        $birthday = $data[14];
        $album_paths = $data[15];

        $hot_db = \Users::getHotWriteCache();
        $login_name = $yuanfen_id . '@365yf.com';
        if ($this->hasCreate($yuanfen_id, $login_name)) {
            return false;
        }

        $hot_db->zadd("yuanfen_ids", time(), $yuanfen_id);

        $album_urls = array();
        if (isPresent($album_paths)) {
            $albums = json_decode($album_paths, true);

            foreach ($albums as $album) {
                $album_urls[] = $this->generateCdnUrl($album);
            }
        }

        $user = new \Users();
        $user->login_name = $login_name;
        $user->user_type = USER_TYPE_SILENT;
        $user->user_status = USER_STATUS_ON;
        $user->product_channel_id = 1;
        $user->platform = isPresent($platform) ? $platform : $platforms[mt_rand(0, 1)];
        $user->sex = $sex;
        $user->nickname = $nickname;
        $user->sid = $user->generateSid('d.');
        $user->fr = 'yuanfen';
        $province = \Provinces::findFirstByName($province_name);
        if ($province) {
            $user->province_id = $province->id;
        }
        $city = \Cities::findFirstByName($city_name);
        if ($city) {
            $user->city_id = $city->id;
        }
        $user->ip = $ip;
        $user->platform_version = $platform_version;
        $user->latitude = $latitude;
        $user->longitude = $longitude;
        $user->height = $height;
        $user->birthday = $birthday;
        $user->created_at = time();

        if ($avatar_path) {
            $avatar_url = $this->generateCdnUrl($avatar_path);
            info('avatar_url', $avatar_url);
            $res = httpGet($avatar_url);
            if ($res === false || $res->code != 200) {
                $avatar_data = explode('/', $avatar_path);
                $new_datas[] = $avatar_data[0];
                $new_datas[] = $avatar_data[1];
                $new_datas[] = 'big_' . $avatar_data[2];
                $new_avatar = implode('/', $new_datas);
                $avatar_url = $this->generateCdnUrl($new_avatar);
                $res = httpGet($avatar_url);
            }
            if ($res === false || $res->code != 200) {
                return false;
            }

            $source_filename = APP_ROOT . 'temp/avatar_' . md5(uniqid(mt_rand())) . '.jpg';
            $dest_filename = APP_NAME . '/avatar/' . date('Ymd') . md5(uniqid(mt_rand())) . '.jpg';
            $f = fopen($source_filename, 'w');
            fwrite($f, $res);
            $avatar_res = \StoreFile::upload($source_filename, $dest_filename);
            unlink($source_filename);
            if ($avatar_res == false) {
                return false;
            }
            $user->avatar = $avatar_res;
        }

        if ($user->save()) {
            $hot_db->zadd("wait_auth_users", time(), $user->id);
            if (count($album_urls) > 0) {
                foreach ($album_urls as $album_url) {
                    \Albums::createAlbum($album_url, $user->id, AUTH_WAIT);
                }
            }
            info("create npc " . $user->id);
            return $user;
        }
        return false;
    }

    function isFinished()
    {
        return $this->silent_num > \Yuanfen::$SILENT_NUM_LIMIT;
    }

    function hasCreate($yuanfen_id, $login_name)
    {
        $hot_db = \Users::getHotWriteCache();
        if (intval($hot_db->zscore("yuanfen_ids", $yuanfen_id)) > 0) {
            return true;
        }
        $user = \Users::findFirstByLoginName($login_name);
        return isPresent($user);
    }

    function cdnDomain()
    {
        if ($this->from_dev) {
            return "cdndevimg.365yf.com";
        }
        return "cdnimg.365yf.com";
    }

    function generateCdnUrl($path)
    {
        $full_path = preg_match('/^\//', $path) ? $path : "/" . $path;

        $full_path = $this->aliyunCdnSign($full_path);
        $host = self::cdnDomain();

        $url = "https://" . $host . $full_path;
        return $url;
    }

    function cdnSignKey()
    {
        if ($this->from_dev) {
            return env('dev_aliyun_cdn_sign_key');
        }
        return env('aliyun_cdn_sign_key');
    }

    function aliyunCdnSign($full_path)
    {
        $auth_key = $this->cdnSignKey();
        $ts = time() + 60 * 60;
        $time_str = date('YmdHi', $ts);
        $source = $auth_key . $time_str . $full_path;
        $md5hash = md5($source);
        $result = '/' . $time_str . '/' . $md5hash . $full_path;
        return $result;
    }

    static function selectUserForReplace()
    {
        $hot_db = \Users::getHotWriteCache();
        $key = "wait_for_replace_users";
        $user = null;
        while (true) {
            $user_id = $hot_db->zrange($key, 0, 0);
            if (isBlank($user_id)) {
                break;
            }
            $user = \Users::findById($user_id);
            if (isBlank($user) || $user->isActive() || isPresent($user->avatar) || $user->fr == 'yuanfen'
                || preg_match('/@365yf.com$/', $user->login_name)
            ) {
                $hot_db->zrem($key, $user_id);
                continue;
            }
            break;
        }
        return $user;
    }

    static function addSilentUser($user)
    {
        $hot_db = \Users::getHotWriteCache();
        $key = "wait_for_replace_users";
        $hot_db->zadd($key, $user->id, $user->id);
    }
}
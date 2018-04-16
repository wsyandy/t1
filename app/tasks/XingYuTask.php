<?php

class XingYuTask extends \Phalcon\Cli\Task
{
    function companyIpAction()
    {
        $company_ip = '116.226.124.47';
        $users = \Users::findByIp($company_ip);
        foreach ($users as $user) {
            $user->organisation = USER_ORGANISATION_COMPANY;
            $user->update();
            info('id:' . $user->id, '，员工昵称：' . $user->nickname, '，所属组织：', $user->organisation,'ip地址：'.$user->ip);
        }
    }

    function grabTryAction()
    {
        static $nickname_count = 0;
        static $number = 1;
        $url = 'http://www.oicq88.com/yingwen/' . $number . '.htm';
        $html_info = file_get_contents($url);
        $html_info = $this->compress_html($html_info);
//        info($html_info);return;
        preg_match_all('/<ul class="list">(.*)<\/ul><\/div><div class="page">/', $html_info, $match);
        preg_match_all('/<li><p>(.*)<\/p><\/li>/', $match[1][0], $match1);
        $html_info_array = explode('</p></li><li><p>', $match1[1][0]);
        $nickname_count += count($html_info_array);
        $html_info_string = implode("\r\n", $html_info_array);
        $html_info_string = $html_info_string . "\r\n";

        $file_name = 'grab_nickname_from_unknow.log';
        $f = fopen($file_name, 'a+');
        $result = fwrite($f, $html_info_string);
        info($nickname_count);
        if ($result) {
            if ($nickname_count < 60) {
                $number++;
                $this->grabTryAction();
            } else {
                info('采集完毕');
            }
        } else {
            info('写入失败');
        }
    }

    function compress_html($string)
    {
        $string = str_replace("\r\n", '', $string); //清除换行符
        $string = str_replace("\n", '', $string); //清除换行符
        $string = str_replace("\t", '', $string); //清除制表符
        $pattern = array(
            "/> *([^ ]*) *</", //去掉注释标记
            "/[\s]+/",
            "/<!--[^!]*-->/",
            "/\" /",
            "/ \"/",
            "'/\*[^*]*\*/'"
        );
        $replace = array(
            ">\\1<",
            " ",
            "",
            "\"",
            "\"",
            ""
        );
        return preg_replace($pattern, $replace, $string);
    }

    function avatarAction()
    {
        $avatar_file_name = 'avatar_url_sex_1.log';
        $f = fopen($avatar_file_name, 'r');
        $avatar = fgets($f);
        fseek($f, 0);
        info(fgets($f));

    }

    function initUserForSexAction()
    {
        //头像
        $avatar_file_name_for_male = 'avatar_url_sex_1.log';
        $avatar_file_name_for_female = 'avatar_url_sex_2.log';
        $avatar_file_name_unknow = 'avatar_url_sex_3.log';

        //昵称
        $nickname_file_name_for_male = 'grab_nickname_from_male.log';
        $nickname_file_name_for_female = 'grab_nickname_from_female.log';
        $nickname_file_name_for_unknow = 'grab_nickname_from_unknow.log';

        //性别
        //USER_SEX_MALE : USER_SEX_FEMALE
        $sex_male = USER_SEX_MALE;
        $sex_female = USER_SEX_FEMALE;

        $this->initUsers($avatar_file_name_for_female, $nickname_file_name_for_female, $sex_female);

//        $file_array = [
//            'avatar_url_sex_1.log'=>'grab_nickname_from_male.log',
//            'avatar_url_sex_2.log'=>'grab_nickname_from_female.log',
//            'avatar_url_sex_3.log'=>'grab_nickname_from_unknow.log',
//        ];
//        $sex = USER_SEX_FEMALE;
//        foreach ($file_array as $avatar_file=>$nickname_file){
//            if(mb_strstr('1',$avatar_file)){
//                $sex = USER_SEX_MALE;
//            }
//            $this->initUsers($avatar_file, $nickname_file, $sex);
//        }

    }


    function initUsers($avatar_file, $nickname_file, $sex)
    {

        $f_avatar = fopen($avatar_file, 'r');
        $f_nickname = fopen($nickname_file, 'r');

        while ($avatar = fgets($f_avatar)) {
            $nickname = fgets($f_nickname);
            $avatar = str_replace("\r\n", '', $avatar); //清除换行符
            $avatar_url = trim($avatar);
            $source_filename = APP_ROOT . 'temp/avatar_' . md5(uniqid(mt_rand())) . '.jpg';
            if (!httpSave($avatar_url, $source_filename)) {
                info('get avatar error', $avatar_url);
                continue;
            }
            $nickname = str_replace("\r\n", '', $nickname);
            $user = new \Users();
            $user->user_type = USER_TYPE_SILENT;
            $user->user_status = USER_STATUS_OFF;
            $user->sex = $sex;
            $user->product_channel_id = 1;
            $user->login_name = '';
            $user->nickname = $nickname;
            $user->platform = '';
            $user->province_id = 0;
            $user->city_id = 0;
            $user->ip = '';
            $user->mobile = '';
            $user->device_id = 0;
            $user->push_token = '';
            $user->version_code = '';
            $user->openid = '';
            $user->password = '';
            $user->fr = '';
            $user->partner_id = 0;
            $user->subscribe = 0;
            $user->event_at = 0;
            $user->latitude = 0;
            $user->longitude = 0;
            $user->geo_province_id = 0;
            $user->geo_city_id = 0;
            $user->ip_province_id = 0;
            $user->ip_city_id = 0;
            $user->register_at = 0;
            $user->mobile_operator = 0;
            $user->api_version = '';
            $user->monologue = '';
            $user->room_id = 0;
            $user->height = 0;
            $user->interests = '';
            $user->gold = 0;
            $user->diamond = 0;
            $user->birthday = 0;
            $user->current_room_seat_id = 0;
            $user->user_role = 0;
            $user->current_room_id = 0;
            $user->geo_hash = '';
            $user->platform_version = '';
            $user->version_name = '';
            $user->manufacturer = '';
            $user->device_no = '';
            $user->client_status = 0;
            $user->user_role_at = 0;
            $user->hi_coins = 0;
            $user->third_unionid = '';
            $user->login_type = '';
            $user->save();
            $user->sid = $user->generateSid('s');
            $user->update();
            $user->updateAvatar($source_filename);


            if (file_exists($source_filename)) {
                unlink($source_filename);
            }
            info($user->id, '用户昵称：' . $user->nickname, '用户头像：' . $user->avatar, '用户的性别：' . $user->sex_text);
        }

        fclose($f_avatar);
        fclose($f_nickname);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 15/01/2018
 * Time: 15:36
 */

namespace m;

class UsersController extends BaseController
{
    function indexAction()
    {

    }

    function accountAction()
    {
        $products = \Products::findDiamondListByUser($this->currentUser());
        $payment_channels = \PaymentChannels::selectByUser($this->currentUser());

        $selected_product = $products[0];
        $selected_payment_channel = $payment_channels[0];
        $this->view->selected_product = $selected_product;
        $this->view->selected_payment_channel = $selected_payment_channel;
        $this->view->products = $products;
        $this->view->user = $this->currentUser();
        $this->view->payment_channels = $payment_channels;
        $this->view->product_channel = $this->currentProductChannel();
        $this->view->title = '我的账户';
    }

    function levelIntroduceAction()
    {
        $code = $this->params('code');
        $file_name = $code . '_level_introduce';
        $file_path = APP_ROOT . 'app/views/m/users/' . $file_name . '.volt';
        $time = time();
        $activity_time = strtotime(date('Y-05-01'), $time);
        $is_activity_show = false;
        if($time >= $activity_time){
            $is_activity_show = true;
        }

        if (file_exists($file_path)) {
            $this->pick('m/users/' . $file_name);
            return;
        }
        $this->view->is_activity_show = $is_activity_show;
        $this->view->title = 'Hi荣耀等级介绍';
    }

    function levelInfoAction()
    {
        $current_user = $this->currentUser();

        $level = $current_user->level;
        $segment_text = $current_user->segment_text;
        if (!$segment_text) {
            $segment_text = '无荣耀';
        }
        $need_experience = $current_user->next_level_experience - $current_user->experience;
        $skip_url = '/m/users/ruanyuyin_level_introduce';

        $this->view->level = $level;
        $this->view->segment_text = $segment_text;
        $this->view->need_experience = round($need_experience);
        $this->view->skip_url = $skip_url;
    }


    function recommendAction()
    {
        $this->view->title = "感兴趣的人";

        $code = $this->params('code');
        $sid = $this->params('sid');

        $file_name = $code . '_recommend';
        $file_path = APP_ROOT . 'app/views/m/users/' . $file_name . '.volt';
        if (file_exists($file_path)) {
            $this->pick('m/users/' . $file_name);
            return;
        }

        $this->view->sid = $sid;
        $this->view->code = $code;
    }

    function ruanyuyinLevelIntroduceAction()
    {
        $this->view->title = '荣耀等级介绍';
    }

    function userListAction()
    {
        if ($this->request->isAjax()) {
            $current_user = $this->currentUser();
            $page = $this->params('page');
            $per_page = $this->params('per_page');
            $user_list = \Users::recommend($current_user, $page, $per_page);

            $res = [];
            if (count($user_list)) {

                $res = $user_list->toJson('user_list', 'toRecommendJson');
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
        }
    }

    function addFriendAction()
    {
        if ($this->request->isAjax()) {
            $current_user = $this->currentUser();
            $self_introduce = $this->params('self_introduce', '您好');
            $user_id = $this->params('user_id');

            $user = \Users::findFirstById($user_id);

            if (isBlank($user)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
            }

            debug($user->id, $self_introduce);

            $current_user->addFriend($user, ['self_introduce' => $self_introduce]);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }
    }

    function voiceAction()
    {
        $user = $this->currentUser();
        $this->view->user = json_encode($user->toChatJson(), JSON_UNESCAPED_UNICODE);
    }

    function recordingAction()
    {
        $user = $this->currentUser();
        $sex = $this->params('sex');
        $nickname = $this->params('nickname');
        $all_read_test = ['可能我只是你生命里的一个过客，但你不会遇见第二个我', '当我们搬开别人脚下的绊脚石时,也许恰恰是在为自己铺路',
            '如果你看到面前的阴影，别怕，那是因为你的背后有阳光！', '玫瑰你的，巧克力你的，钻石你的。你，是我的！',
            '为什么我的眼里常含泪水，因为我的同桌老给我丢人。', '最怕那些对我很重要的人连句再见都没有就突然消失不见',
            '喜欢这种东西,捂住嘴巴,也会从眼睛里跑出来', '这是个爱宠物的时代，我养了两只，一只是老婆，一只是叫儿子。',
            '我竟然还能见到小姐这样的清纯可人，无疑是我这辈子最大的幸运。', '我知道你生气了，而且你每次生气我都好害怕，理解我，好吗?原谅我，好吗?'];

        $read_text_index = array_rand($all_read_test);
        $this->view->read_text = $all_read_test[$read_text_index];
        $this->view->nickname = $nickname;
        $this->view->sex = $sex;
        $this->view->user = json_encode($user->toChatJson(), JSON_UNESCAPED_UNICODE);
    }

    function voiceIdentifyAction()
    {
        $user = $this->currentUser();
        $sex = $this->params('sex');
        $nickname = $this->params('nickname');

        $ip = $this->remoteIp();
        $show_share = false;

        if (in_array($ip, ['116.226.119.13', '116.226.120.117', '139.227.253.40'])) {
            $show_share = true;
        }

        $this->view->sex = $sex;
        $this->view->nickname = $nickname;
        $this->view->user = json_encode($user->toChatJson(), JSON_UNESCAPED_UNICODE);
    }

    function getTonicAction()
    {
        $sex = $this->params('sex');
        $all_tonic_female = ['少女音', '萝莉音', '少萝音', '少御音', '御姐音'];
        $all_tonic_male = ['青年音', '正太音', '少年音', '暖男音', '青受音'];
        if ($sex) {
            $tonic = $all_tonic_male[array_rand($all_tonic_male)];
        } else {
            $tonic = $all_tonic_female[array_rand($all_tonic_female)];
        }

        $tonic_ratio = $tonic_ratio = mt_rand(50, 59);
        $avatar_url = \Users::getTonicAvatar($tonic);

        $data = [
            'tonic' => $tonic,
            'tonic_ratio' => $tonic_ratio,
            'avatar_url' => $avatar_url
        ];
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $data);

    }


    function getConsonantsAction()
    {
        $sex = $this->params('sex');
        $tonic_ratio = $this->params('tonic_ratio');
        $all_consonant_male = ['慵懒温润青受音', '清秀气质青年音', '咩咩小受音',
            '奶油小生音', '慵懒青受音', '醇厚魅力青叔音',
            '呆萌怪蜀黍音', '高冷霸道总裁音', '木讷呆萌音 ',
            '磁性青年音', '低沉磁性青叔音', '淡淡烟草小沙音',
            '逗比青年音', '潜质男神', '憨厚撩人小沙哑', '香醇特仑苏音',
            '木讷呆萌音', '清晨阳关含笑音', '阳光暖男音',
            '高冷磁性男神音', '清新稚嫩学弟音', '磁性感性青年音',
            '单纯可爱少年音'];

        $all_consonant_female = ['潜在智齿少女音', '气质安哑少御音', '憧憬撩人女神音',
            '温婉舒适少女音', '低沉暗哑少女音', '娇憨卖萌音',
            '清甜温婉音', '甜蜜糖果音', '温柔可爱萝莉音', '清冷少御音',
            '美艳少妇音', '郁郁寡欢少妇音', '娇俏傲娇学妹音',
            '调皮学妹音', '千金小姐音', '可爱清纯女神音',
            '迷之魔幻女神音', '女王范音', '知心学姐音', '温柔女神音',
            '温温柔柔姐姐音', '可爱拖拉音', '傲娇甜嗔酥麻音', '元气美少女音',
            '撩人小磕音', '空灵舒服玻璃音', '甜美乖张含笑音',
            '山间黄鹂吟鸣音', '隐藏清新女神音', '可爱小家碧玉音', '清爽雨后小甜音'];

        $consonant_ratios = \Users::getRatio($tonic_ratio);
        if (!$consonant_ratios) {
            $consonant_ratios = \Users::getRatio($tonic_ratio);
        }

        if ($sex) {
            $consonant_index = array_rand($all_consonant_male, 3);
            $consonant1 = [$consonant_ratios[0] => $all_consonant_male[$consonant_index[0]]];
            $consonant2 = [$consonant_ratios[1] => $all_consonant_male[$consonant_index[1]]];
            $consonant3 = [$consonant_ratios[2] => $all_consonant_male[$consonant_index[2]]];
        } else {
            $consonant_index = array_rand($all_consonant_female, 3);
            $consonant1 = [$consonant_ratios[0] => $all_consonant_female[$consonant_index[0]]];
            $consonant2 = [$consonant_ratios[1] => $all_consonant_female[$consonant_index[1]]];
            $consonant3 = [$consonant_ratios[2] => $all_consonant_female[$consonant_index[2]]];
        }

        $consonants = [
            'consonant1' => $consonant1,
            'consonant2' => $consonant2,
            'consonant3' => $consonant3
        ];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $consonants);

    }

    function getPropertyAction()
    {
        $sex = $this->params('sex');
        $all_property = ['攻', '守'];
        $property = $all_property[array_rand($all_property)];

        $male_mate = ['萝莉音', '少萝音', '少女音', '少御音', '御姐音'];
        $female_mate = ['少年音', '青年音', '正太音', '暖男音', '青受音'];
        if ($sex) {
            $mate = $male_mate[array_rand($male_mate)];
        } else {
            $mate = $female_mate[array_rand($female_mate)];
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['property' => $property, 'mate' => $mate]);

    }

    function getCharmValueAction()
    {
        $heartbeat_value = mt_rand(80, 99) / 10;
        $flirt_value = mt_rand(80, 99) / 10;
        $fall_down_value = mt_rand(80, 99) / 10;
        $grade = 0;

        if ($heartbeat_value >= 9.5 && $flirt_value >= 9.5 && $fall_down_value >= 9.5) {
            $grade = 1;
        }

        $datas = [
            'heartbeat_value' => $heartbeat_value,
            'flirt_value' => $flirt_value,
            'fall_down_value' => $fall_down_value,
            'grade' => $grade
        ];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $datas);
    }

    function saveImageAction()
    {
        $image_data = $this->params('image_data');
        $image_data = trim($image_data);
        $user = $this->currentUser();

        //data:image/octet-stream;base64
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $image_data, $result)) {
            $type = $result[2];
            echoLine($type);
            if (in_array($type, array('pjpeg', 'jpeg', 'jpg', 'gif', 'bmp', 'png'))) {
                $new_file = $source_filename = APP_ROOT . 'temp/voice_identify_' . md5(uniqid(mt_rand())) . '.jpg';
                if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $image_data)))) {
                    $img_path = str_replace('../../..', '', $new_file);
                    $img_files[] = $img_path;
                    $res = \Albums::uploadImage($user, $img_files);
                    if (file_exists($source_filename)) {
                        unlink($source_filename);
                    }
                    if ($res) {
                        return $this->renderJSON(ERROR_CODE_SUCCESS, '图片已成功保存到Hi相册');
                    } else {
                        return $this->renderJSON(ERROR_CODE_FAIL, '图片保存失败');
                    }
                } else {
                    return $this->renderJSON(ERROR_CODE_FAIL, '图片生成失败');
                }
            } else {
                //文件类型错误
                return $this->renderJSON(ERROR_CODE_FAIL, '图片上传类型错误');
            }

        } else {
            //文件错误
            return $this->renderJSON(ERROR_CODE_FAIL, '文件错误');
        }
    }
}
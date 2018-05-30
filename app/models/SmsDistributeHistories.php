<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/5/27
 * Time: 上午11:29
 */
class SmsDistributeHistories extends BaseModel
{

    /**
     * @type ProductChannels
     */
    private $_product_channel;
    /**
     * @type ShareHistories
     */
    private $_share_history;
    /**
     * @type Users
     */
    private $_user;


    static $STATUS = [AUTH_WAIT => '等待', AUTH_SUCCESS => '注册成功', AUTH_FAIL => '老用户'];

    function afterCreate()
    {

//        // 激活手机号次数
//        $attrs = $this->getStatAttrs();
//        \Stats::delay()->record('user', 'sem_sms_active', $attrs);
//        // 激活手机号个数
//        $attrs['id'] = $this->mobile;
//        \Stats::delay()->record('user', 'sem_sms_active_mobile', $attrs);
    }

    function afterUpdate()
    {

    }

    public function getStatAttrs()
    {

        $stat_keys = ['id', 'product_channel_id'];
        $hash = [];
        foreach ($stat_keys as $key) {
            $hash[$key] = $this->$key;
        }

        $hash['created_at'] = intval($this->created_at);
        $hash['stat_at'] = time();

        return $hash;
    }

    static function checkMobile($mobile)
    {

        $start_at = beginOfDay();
        $conds = ['conditions' => 'created_at<:start_at: and status=:status: and mobile=:mobile:',
            'bind' => ['start_at' => $start_at, 'status' => AUTH_WAIT, 'mobile' => $mobile]];

        $histories = self::find($conds);
        foreach ($histories as $history) {
            $history->status = AUTH_FAIL;
            $history->save();
        }
    }

    static function checkRegister($current_user, $mobile, $opts = [])
    {

        $password = fetch($opts, 'password');
        $ip = fetch($opts, 'ip');

        $conds = ['conditions' => 'product_channel_id = :product_channel_id: and mobile=:mobile: and status=:status:',
            'bind' => ['product_channel_id' => $current_user->product_channel_id, 'mobile' => $mobile, 'status' => AUTH_WAIT],
            'order' => 'id desc'];

        $sms_distribute_history = self::findFirst($conds);
        if (!$sms_distribute_history) {
            return [ERROR_CODE_FAIL, '无效账户'];
        }

        if ($sms_distribute_history->password != md5($password)) {
            return [ERROR_CODE_FAIL, '密码错误'];
        }


        if(!$current_user->isCompanyUser() && isProduction()){

            $stat_db = \Stats::getStatDb();
            if ($stat_db->zscore('sms_distribute_history_register_device_ids', $current_user->device_id)) {
                info('false sms_distribute_history_register_device_ids', $current_user->id, $mobile, $current_user->device_id, $ip);
                return [ERROR_CODE_FAIL, '该设备已分销'];
            }

            if ($stat_db->zscore('sms_distribute_history_register_ips', $ip)) {
                info('false sms_distribute_history_register_ips', $current_user->id, $mobile, $current_user->device_id, $ip);
                return [ERROR_CODE_FAIL, '该设备已分销！'];
            }


            $stat_db->zadd('sms_distribute_history_register_device_ids', time(), $current_user->device_id);
            $stat_db->zadd('sms_distribute_history_register_ips', time(), $ip);
        }

        $sms_distribute_history->user_id = $current_user->id;
        $sms_distribute_history->status = AUTH_SUCCESS;
        $sms_distribute_history->update();

        $current_user->mobile = $sms_distribute_history->mobile;
        $current_user->share_parent_id = $sms_distribute_history->share_user_id;
        $current_user->password = $sms_distribute_history->password;
        $current_user->update();

        $share_user_id = $sms_distribute_history->share_user_id;
        $share_user = \Users::findFirstById($share_user_id);
        if ($share_user) {

            $amount = 10;
            $opts = ['remark' => '分销注册奖励钻石' . $amount, 'mobile' => $share_user->mobile, 'target_id' => $current_user->id];
            $result = \AccountHistories::changeBalance($share_user, ACCOUNT_TYPE_DISTRIBUTE_REGISTER, $amount, $opts);

            $stat_db = \Stats::getStatDb();
            $distribute_bonus_key = self::generateDistributeBonusKey();
            if ($result) {
                $stat_db->hincrby($distribute_bonus_key, 'register_distribute_bonus', $amount);
            }
        }

        return [ERROR_CODE_SUCCESS, '成功'];
    }


    // type: pay / exchange
    static function checkPay($current_user, $amount, $type = 'pay')
    {

        if (is_numeric($current_user)) {
            $current_user = Users::findFirstById($current_user);
        }

        if (!$current_user->share_parent_id) {
            return [ERROR_CODE_FAIL, '非分销用户'];
        }

        $first_user_id = $current_user->share_parent_id;
        $first_user = \Users::findFirstById($first_user_id);
        if ($first_user) {

            $stat_db = \Stats::getStatDb();
            $distribute_bonus_key = self::generateDistributeBonusKey();

            $bonus_amount = round($amount * 0.05);

            if ($type == 'pay') {
                $opts = ['remark' => '分销充值奖励钻石' . $bonus_amount, 'mobile' => $first_user->mobile, 'target_id' => $current_user->id];
                $result = \AccountHistories::changeBalance($first_user, ACCOUNT_TYPE_DISTRIBUTE_PAY, $bonus_amount, $opts);
            } else {
                $opts = ['remark' => '分销兑换奖励钻石' . $bonus_amount, 'mobile' => $first_user->mobile, 'target_id' => $current_user->id];
                $result = \AccountHistories::changeBalance($first_user, ACCOUNT_TYPE_DISTRIBUTE_EXCHANGE, $bonus_amount, $opts);
            }

            if ($result) {
                $stat_db->hincrby($distribute_bonus_key, 'first_distribute_bonus', $bonus_amount);
            }

            $second_user = Users::findFirstById($first_user->share_parent_id);
            if ($second_user) {

                $second_bonus_amount = round($amount * 0.01);
                if ($type == 'pay') {
                    $last_opts = ['remark' => '下级分销充值奖励钻石' . $second_bonus_amount, 'mobile' => $second_user->mobile, 'target_id' => $current_user->id];
                    $second_result = \AccountHistories::changeBalance($second_user, ACCOUNT_TYPE_DISTRIBUTE_PAY, $second_bonus_amount, $last_opts);
                } else {
                    $last_opts = ['remark' => '下级分销兑换奖励钻石' . $second_bonus_amount, 'mobile' => $second_user->mobile, 'target_id' => $current_user->id];
                    $second_result = \AccountHistories::changeBalance($second_user, ACCOUNT_TYPE_DISTRIBUTE_EXCHANGE, $second_bonus_amount, $last_opts);
                }
                if ($second_result) {
                    $stat_db->hincrby($distribute_bonus_key, 'second_distribute_bonus', $second_bonus_amount);
                }
            }
        }

        return [ERROR_CODE_SUCCESS, '成功'];
    }


    static function findFirstByMobile($product_channel, $mobile)
    {
        $user = \SmsDistributeHistories::findFirst([
            'conditions' => 'product_channel_id = :product_channel_id: and mobile=:mobile:',
            'bind' => ['product_channel_id' => $product_channel->id, 'mobile' => $mobile],
            'order' => 'id desc'
        ]);

        return $user;
    }

    //按天统计一、二级钻石奖励统计
    static function generateDistributeBonusKey($time = '')
    {
        if (!$time) {
            $time = time();
        }

        return 'distribute_bonus_' . date('Ymd', $time);
    }

    static function generateDistributeNumKey($time = '')
    {
        if (!$time) {
            $time = time();
        }
        return 'distribute_share_num_' . date('Ymd', $time);
    }

    static function generateShareDistributeUserListKey($time = '')
    {
        if (!$time) {
            $time = time();
        }
        return 'share_distribute_user_list' . date('Ymd', $time);
    }

    //统计分销分享次数
    static function shareDistributeNum()
    {
        $stat_db = \Stats::getStatDb();
        $share_num_key = \SmsDistributeHistories::generateDistributeNumKey();
        $stat_db->incr($share_num_key);
    }


    //统计分销分享人数
    static function shareDistributeUserNum($user_id)
    {
        $stat_db = \Stats::getStatDb();
        $share_user_list_key = \SmsDistributeHistories::generateShareDistributeUserListKey();
        $stat_db->zadd($share_user_list_key, time(), $user_id);
    }
}
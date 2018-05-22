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

    static function isUserForShare($opts)
    {
        $product_channel_id = fetch($opts, 'product_channel_id');
        $mobile = fetch($opts, 'mobile');
        $type = fetch($opts, 'type', 'register');
        $amount = fetch($opts, 'amount');
        $current_user = fetch($opts, 'current_user');
        $status = $type == 'register' ? AUTH_WAIT : AUTH_SUCCESS;
        $conds = ['conditions' => 'product_channel_id = :product_channel_id: and status=:status: and mobile=:mobile:',
            'bind' => ['status' => $status, 'mobile' => $mobile, 'product_channel_id' => $product_channel_id],
            'order' => 'id desc'];
        $sms_distribute_history = self::findFirst($conds);
        info($type, '=>', $mobile);

        if ($sms_distribute_history) {
            switch ($type) {
                case 'register':
                    self::distributeRegisterBonus($sms_distribute_history, $current_user);
                    return true;
                case 'pay':
                    self::distributePayBonus($sms_distribute_history, $amount);
                    return true;
                case 'exchange':
                    self::distributeExchangeBonus($sms_distribute_history, $amount);
                    return true;
            }
        }

        return false;
    }

    static function distributeRegisterBonus($sms_distribute_history, $current_user)
    {
        $sms_distribute_history->user_id = $current_user->id;
        $sms_distribute_history->status = AUTH_SUCCESS;
        $sms_distribute_history->update();

        $current_user->mobile = $sms_distribute_history->mobile;
        $current_user->share_parent_id = $sms_distribute_history->share_user_id;
        $current_user->password = $sms_distribute_history->password;
        $current_user->update();

        $user_id = $sms_distribute_history->share_user_id;
        $user = \Users::findFirstById($user_id);
        if ($user) {
            $amount = 10;
            $opts = ['remark' => '分销注册奖励钻石' . $amount, 'mobile' => $user->mobile, 'target_id' => $sms_distribute_history->id];
            \AccountHistories::changeBalance($user, ACCOUNT_TYPE_DISTRIBUTE_REGISTER, $amount, $opts);
        }
    }

    static function distributePayBonus($sms_distribute_history, $amount)
    {
        $user_id = $sms_distribute_history->share_user_id;
        $user = \Users::findFirstById($user_id);
        if ($user) {
            $stat_db = \Stats::getStatDb();
            $distribute_bonus_key = self::generateDistributeBonusKey();

            $bonus_amount = round($amount * 0.05);
            $opts = ['remark' => '分销充值奖励钻石' . $bonus_amount, 'mobile' => $user->mobile, 'target_id' => $sms_distribute_history->id];
            $result = \AccountHistories::changeBalance($user, ACCOUNT_TYPE_DISTRIBUTE_PAY, $bonus_amount, $opts);
            if ($result) {
                $stat_db->hincrby($distribute_bonus_key, 'first_distribute_bonus', $bonus_amount);
            }

            if ($user->share_parent_id) {
                $top_user = \Users::findFirstById($user->share_parent_id);
                if (isPresent($top_user)) {
                    $second_bonus_amount = round($amount * 0.01);
                    $last_opts = ['remark' => '底层分销充值奖励钻石' . $second_bonus_amount, 'mobile' => $top_user->mobile, 'target_id' => $sms_distribute_history->id];
                    $second_result = \AccountHistories::changeBalance($top_user, ACCOUNT_TYPE_DISTRIBUTE_PAY, $second_bonus_amount, $last_opts);
                    if ($second_result) {
                        $stat_db->hincrby($distribute_bonus_key, 'second_distribute_bonus', $second_bonus_amount);
                    }
                }
            }
        }
    }

    static function distributeExchangeBonus($sms_distribute_history, $amount)
    {
        $user_id = $sms_distribute_history->share_user_id;
        $user = \Users::findFirstById($user_id);
        if ($user) {
            $stat_db = \Stats::getStatDb();
            $distribute_bonus_key = self::generateDistributeBonusKey();

            $bonus_amount = round($amount * 0.05);
            $opts = ['remark' => '分销兑换奖励钻石' . $bonus_amount, 'mobile' => $user->mobile, 'target_id' => $sms_distribute_history->id];
            $result = \AccountHistories::changeBalance($user, ACCOUNT_TYPE_DISTRIBUTE_EXCHANGE, $bonus_amount, $opts);
            if ($result) {
                $stat_db->hincrby($distribute_bonus_key, 'first_distribute_bonus', $bonus_amount);
            }

            if ($user->share_parent_id) {
                $top_user = \Users::findFirstById($user->share_parent_id);
                if (isPresent($top_user)) {
                    $second_bonus_amount = round($amount * 0.01);
                    $last_opts = ['remark' => '底层分销兑换奖励钻石' . $second_bonus_amount, 'mobile' => $top_user->mobile, 'target_id' => $sms_distribute_history->id];
                    $second_result = \AccountHistories::changeBalance($top_user, ACCOUNT_TYPE_DISTRIBUTE_EXCHANGE, $second_bonus_amount, $last_opts);
                    if ($second_result) {
                        $stat_db->hincrby($distribute_bonus_key, 'second_distribute_bonus', $second_bonus_amount);
                    }
                }
            }
        }
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
        return 'distribute_bonus_' . date('Ymd', beginOfDay($time));
    }

    static function generateDistributeNumKey($time = '')
    {
        return 'distribute_share_num_' . date('Ymd', beginOfDay($time));
    }
}
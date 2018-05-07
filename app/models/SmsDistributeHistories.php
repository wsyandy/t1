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
        $user_id = fetch($opts, 'user_id');
        $status = $type == 'register' ? AUTH_WAIT : AUTH_SUCCESS;
        $conds = ['conditions' => 'product_channel_id = :product_channel_id: and status=:status: and mobile=:mobile:',
            'bind' => ['status' => $status, 'mobile' => $mobile, 'product_channel_id' => $product_channel_id],
            'order' => 'id desc'];
        $sms_distribute_history = self::findFirst($conds);
        if ($sms_distribute_history) {
            switch ($type) {
                case 'register':
                    self::distributeRegisterBonus($sms_distribute_history, $user_id);
                    info($type, '=>', $mobile);
                    return true;
                case 'pay':
                    self::distributePayBonus($sms_distribute_history, $amount);
                    info($type, 'pay=>', $mobile);
                    return true;
            }
        }
        info($type, 'pay=>', $mobile);
        return false;
    }

    static function distributeRegisterBonus($sms_distribute_history, $user_id)
    {
        $sms_distribute_history->user_id = $user_id;
        $sms_distribute_history->status = AUTH_SUCCESS;
        $sms_distribute_history->update();

        $user_id = $sms_distribute_history->share_user_id;
        $user = \Users::findFirstById($user_id);
        if ($user) {
            $amount = 20;
            $opts = ['remark' => '分销注册奖励钻石' . $amount, 'mobile' => $user->mobile, 'target_id' => $sms_distribute_history->id];
            \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DISTRIBUTE_REGISTER, $amount, $opts);
        }
    }

    static function distributePayBonus($sms_distribute_history, $amount)
    {
        $user_id = $sms_distribute_history->share_user_id;
        $user = \Users::findFirstById($user_id);
        if ($user) {
            $bonus_amount = intval($amount * 0.05);
            $opts = ['remark' => '分销充值奖励钻石' . $bonus_amount, 'mobile' => $user->mobile, 'target_id' => $sms_distribute_history->id];
            \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DISTRIBUTE_PAY, $bonus_amount, $opts);
        }
    }

    static function findFirstByMobile($product_channel, $mobile)
    {
        $user = \Users::findFirst([
            'conditions' => 'product_channel_id = :product_channel_id: and mobile=:mobile:',
            'bind' => ['product_channel_id' => $product_channel->id, 'mobile' => $mobile],
            'order' => 'id desc'
        ]);

        return $user;
    }
}
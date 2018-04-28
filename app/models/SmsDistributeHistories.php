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
        $product_channel = fetch($opts, 'product_channel');
        $mobile = fetch($opts, 'mobile');
        $type = fetch($opts, 'type', 'login');
        $amount = fetch($opts, 'amount');
        $status = $type == 'login' ? AUTH_WAIT : AUTH_SUCCESS;
        $conds = ['conditions' => 'product_channel_id = :product_channel_id: and status=:status: and mobile=:mobile:',
            'bind' => ['status' => $status, 'mobile' => $mobile, 'product_channel_id' => $product_channel->id],
            'order' => 'id desc'];
        $share_user = \SmsDistributeHistories::findFirst($conds);
        if ($share_user) {
            switch ($type) {
                case 'login':
                    \SmsDistributeHistories::distributeRegisterBonus($share_user);
                    break;
                case 'pay':
                    \SmsDistributeHistories::distributePayBonus($share_user, $amount);
                    break;
            }

        }
        return null;
    }

    static function distributeRegisterBonus($share_user)
    {
        $share_user->status = AUTH_SUCCESS;
        $share_user->update();

        $user_id = $share_user->share_user_id;
        $user = \Users::findFirstById($user_id);
        if($user){
            $amount = 20;
            $opts = ['remark' => '分销注册奖励钻石' . $amount, 'mobile' => $user->mobile];
            \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DISTRIBUTE_REGISTER, $amount, $opts);
        }

    }

    static function distributePayBonus($share_user, $amount)
    {
        $user_id = $share_user->share_user_id;
        $user = \Users::findFirstById($user_id);
        if($user){
            $bonus_amount = intval($amount * 0.2);
            $opts = ['remark' => '分销充值奖励钻石' . $bonus_amount, 'mobile' => $user->mobile];
            \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DISTRIBUTE_PAY, $bonus_amount, $opts);
        }
    }
}
<?php

class AwardHistories extends BaseModel
{
    /**
     * @type ProductChannels
     */
    private $_product_channel;


    /**
     * @type Users
     */
    private $_user;

    //领取状态
    static $STATUS = [STATUS_ON => '点击领取', STATUS_OFF => '已领取'];

    static $TYPE = [AWARD_DIAMOND => '钻石', AWARD_HI_COIN => 'Hi币'];

    static $AUTH_STATUS = [AUTH_WAIT => '待审核', AUTH_SUCCESS => '审核成功', AUTH_FAIL => '审核失败'];

    static $CONTENT_TYPE = [CHAT_CONTENT_TYPE_TEXT => '文本消息', CHAT_CONTENT_TYPE_TEXT_NEWS => '图文消息'];

    function afterUpdate()
    {
        if ($this->hasChanged('auth_status') && $this->auth_status == AUTH_SUCCESS) {
            $content = '恭喜您获取扶持奖励，快去领取吧！';
            $opts = [
                'image_url' => $this->user->avatar_url,
                'title' => '扶持奖励',
                'url' => 'url://m/award_histories?sid=' . $this->user->sid . '&code=' . $this->product_channel->code . '&award_history_id=' . $this->id
            ];

            \Chats::sendTextNewsSystemMessage($this->user_id, $content, $opts);
        }
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'status_text' => $this->status_text,
            'type_text' => $this->type_text,
            'amount' => $this->amount,
            'created_at_text' => date('Y年m月', strtotime('-1 month', $this->created_at)),
            'status' => $this->status
        ];
    }

    function getAwards($user)
    {
        $type = $this->type;
        switch ($type) {
            case 'diamond':
                $opts = ['remark' => '系统扶持奖励' . $this->amount . $this->type_text, 'target_id' => $this->id];
                $result = \AccountHistories::changeBalance($user, ACCOUNT_TYPE_SYSTEM_AWARD, $this->amount, $opts);
                break;
            case 'hi_coin':
                $opts = ['remark' => '系统扶持奖励' . $this->amount . $this->type_text, 'fee_type' => HI_COIN_FEE_TYPE_UNION_HOST_REWARD, 'hi_coins' => $this->amount];
                $result = \HiCoinHistories::createHistory($user->id, $opts);
                break;
            default:
                $result = null;
                break;
        }

        return $result;
    }
}
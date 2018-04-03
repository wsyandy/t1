<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/6
 * Time: 上午11:58
 */
class WithdrawHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type ProductChannels
     */
    private $_product_channel;

    /**
     * @type Unions
     */
    private $_union;

    static $TYPE = [WITHDRAW_TYPE_USER => '用户体体现', WITHDRAW_TYPE_UNION => '公会体现'];
    static $STATUS = [WITHDRAW_STATUS_WAIT => '提现中', WITHDRAW_STATUS_SUCCESS => '提现成功', WITHDRAW_STATUS_FAIL => '提现失败'];

    function afterUpdate()
    {
        if ($this->hasChanged('status')) {

            if (WITHDRAW_TYPE_USER == $this->type) {

                if (WITHDRAW_STATUS_SUCCESS == $this->status) {
                    $user = $this->user;
                    $content = '提现到账成功！如有疑问请联系官方客服中心400-018-7755解决。';
                    HiCoinHistories::createHistory($user->id, ['withdraw_history_id' => $this->id]);
                }

                if (WITHDRAW_STATUS_FAIL == $this->status) {
                    $content = '提现失败！如有疑问请联系官方客服中心400-018-7755解决。';

                    if ($this->error_reason) {
                        $content = $this->error_reason;
                    }
                }

                Chats::sendTextSystemMessage($this->user_id, $content);
            }

            if (WITHDRAW_TYPE_UNION == $this->type) {

                $union = $this->union;

                if (WITHDRAW_STATUS_SUCCESS == $this->status) {
                    $union->settled_amount += $this->amount;
                    $union->amount = $union->amount - $this->amount;
                }

                $union->frozen_amount = 0; //冻结金额
                $union->save();
            }
        }
    }

    function beforeUpdate()
    {

        if (WITHDRAW_TYPE_USER == $this->type) {

            if (WITHDRAW_STATUS_SUCCESS == $this->status) {

                if ($this->user->hi_coins < $this->amount) {
                    $this->error_reason = '余额不足';
                    return true;
                }
            }
        }

        if (WITHDRAW_TYPE_UNION == $this->type) {

            $union = $this->union;

            if (WITHDRAW_STATUS_SUCCESS == $this->status) {
                if ($union->amount < $this->amount) {
                    return true;
                }
            }
        }

    }

    function afterCreate()
    {
        if (WITHDRAW_TYPE_USER == $this->type) {

            $attrs = $this->user->getStatAttrs();
            $attrs['add_value'] = $this->amount;
            \Stats::delay()->record("user", "withdraw", $attrs);

            Chats::sendTextSystemMessage($this->user_id, '提现申请已提交，等待Hi语音平台处理，每周二当日到账！');
        }
    }

    static function createWithdrawHistories($user, $opts)
    {
        $amount = fetch($opts, 'money');
        $user_name = fetch($opts, 'name');
        $alipay_account = fetch($opts, 'account');

        $max_amount = $user->withdraw_amount;

        if (self::hasWaitedHistoryByUser($user)) {
            return [ERROR_CODE_FAIL, '一周只能提现一次哦'];
        }

        if ($amount > $max_amount) {
            return [ERROR_CODE_FAIL, '提现金额超过可提现最大值'];
        }

        $history = new WithdrawHistories();
        $history->user_id = $user->id;
        $history->user_name = $user_name;
        $history->alipay_account = $alipay_account;
        $history->product_channel_id = $user->product_channel_id;
        $history->amount = $amount;
        $history->status = WITHDRAW_STATUS_WAIT;
        $history->type = WITHDRAW_TYPE_USER;
        $history->save();

        return [ERROR_CODE_SUCCESS, '受理中'];
    }

    static function createUnionWithdrawHistories($union, $opts)
    {
        $amount = fetch($opts, 'amount');
        $alipay_account = fetch($opts, 'alipay_account');

        if (self::hasWaitedHistoryByUnion($union)) {
            return [ERROR_CODE_FAIL, '您有受理中的提现记录，不能再提现'];
        }

        if ($amount > $union->amount) {
            return [ERROR_CODE_FAIL, '提现金额超过可提现最大值'];
        }

        $history = new WithdrawHistories();
        $history->union_id = $union->id;
        $history->alipay_account = $alipay_account;
        $history->product_channel_id = $union->product_channel_id;
        $history->amount = $amount;
        $history->status = WITHDRAW_STATUS_WAIT;
        $history->type = WITHDRAW_TYPE_UNION;
        $history->save();

        $union->frozen_amount = $amount;
        $union->update();

        return [ERROR_CODE_SUCCESS, '受理中'];
    }


    static function search($user, $page, $per_page = 10)
    {
        $cond = [
            'conditions' => ' user_id = :user_id: and product_channel_id = :product_channel_id:',
            'bind' => ['product_channel_id' => $user->product_channel_id, 'user_id' => $user->id],
            'order' => 'id desc'
        ];
        $withdraw_histories = self::findPagination($cond, $page, $per_page);
        return $withdraw_histories;
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'status_text' => $this->status_text,
            'created_at_date' => $this->created_at_date,
            'created_at_text' => $this->created_at_text,
        ];
    }

    static function hasWaitedHistoryByUser($user)
    {
        $withdraw_history = WithdrawHistories::findFirst(
            [
                'conditions' => '(user_id = :user_id: and type = :type: and created_at >= :start: and created_at <= :end:) or '
                    . ' (status = :status: and user_id = :user_id1:)',
                'bind' => ['user_id' => $user->id, 'type' => WITHDRAW_TYPE_USER, 'start' => beginOfWeek(), 'end' => endOfWeek(),
                    'status' => WITHDRAW_STATUS_WAIT, 'user_id1' => $user->id],
                'order' => 'id desc'
            ]
        );

        if ($withdraw_history) {
            return true;
        }

        return false;
    }

    static function hasWaitedHistoryByUnion($union)
    {
        $withdraw_history = WithdrawHistories::findFirst(
            [
                'conditions' => 'status = :status: and union_id = :union_id: and product_channel_id = :product_channel_id: and type = :type:',
                'bind' => ['status' => WITHDRAW_STATUS_WAIT, 'union_id' => $union->id, 'product_channel_id' => $union->product_channel_id, 'type' => WITHDRAW_TYPE_UNION],
                'order' => 'id desc'
            ]
        );

        if ($withdraw_history) {
            return true;
        }

        return false;
    }

    static function exportData($export_history_id, $cond)
    {
        debug($cond);
        $withdraw_histories = self::find($cond);
        $titles = ['日期', '用户id', '姓名', '支付宝账号', '提现金额'];
        $data = [];
        foreach ($withdraw_histories as $withdraw_history) {
            $data[] = [$withdraw_history->created_at_text, $withdraw_history->user_id, $withdraw_history->user_name, $withdraw_history->alipay_account, $withdraw_history->amount];
        }
        $temp_file = APP_ROOT . '/temp/export_withdraw_history_' . date('Ymd') . '.xls';
        $uri = writeExcel($titles, $data, $temp_file, true);

        if ($uri) {
            $export_history = ExportHistories::findFirstById($export_history_id);
            $export_history->file = $uri;
            $export_history->save();
        }
    }

    static function findWaitWithDrawAmount($user_id)
    {
        $conditions = [
            'conditions' => 'user_id = :user_id: and status = :status:',
            'bind' => ['user_id' => $user_id, 'status' => WITHDRAW_STATUS_WAIT],
            'order' => 'id desc'
        ];

        $amount = 0;

        $withdraw_histories = WithdrawHistories::find($conditions);

        foreach ($withdraw_histories as $withdraw_history) {
            $amount += $withdraw_history->amount;
        }

        return $amount;
    }


}
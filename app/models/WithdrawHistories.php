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

    /**
     * @type WithdrawAccounts
     */
    private $_withdraw_account;

    static $TYPE = [WITHDRAW_TYPE_USER => '用户体提现', WITHDRAW_TYPE_UNION => '公会提现'];
    static $STATUS = [WITHDRAW_STATUS_WAIT => '提现中', WITHDRAW_STATUS_SUCCESS => '提现成功', WITHDRAW_STATUS_FAIL => '提现失败'];

    function afterUpdate()
    {
        if ($this->hasChanged('status')) {

            if (WITHDRAW_TYPE_USER == $this->type) {

                if (WITHDRAW_STATUS_SUCCESS == $this->status) {
                    $content = '提现到账成功！如有疑问请联系官方客服中心400-018-7755解决。';

                    //推送提现数据，只要有提现动作就推送，此时为提现到账成功推送
                    \DataCollection::syncData('withdraw_history', 'update_status_success', ['withdraw_history' => $this->toPushDataJson()]);
                }

                if (WITHDRAW_STATUS_FAIL == $this->status) {
                    $content = '提现失败！如有疑问请联系官方客服中心400-018-7755解决。';

                    if ($this->error_reason) {
                        $content = $this->error_reason;
                    }

                    HiCoinHistories::createHistory($this->user->id, ['withdraw_history_id' => $this->id]);
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

            //推送提现数据，目前暂时只做用户提现,只要有提现动作就推送
            \DataCollection::syncData('withdraw_history', 'create', ['withdraw_history' => $this->toPushDataJson()]);
        }
    }

    static function createWithdrawHistory($user, $opts)
    {
        $amount = fetch($opts, 'amount');
        $withdraw_account_id = fetch($opts, 'withdraw_account_id');

        $withdraw_account = WithdrawAccounts::findFirstById($withdraw_account_id);

        if (isBlank($withdraw_account) || $withdraw_account->status != STATUS_ON) {
            return [ERROR_CODE_FAIL, '收款账户错误，请重新选择'];
        }

        $wait_withdraw_history = WithdrawHistories::waitWithdrawHistory($user);

        if ($wait_withdraw_history) {

            if (WITHDRAW_STATUS_WAIT == $wait_withdraw_history->status) {
                return [ERROR_CODE_FAIL, '您有一笔正在提现的订单,请勿重复提现'];
            }

            return [ERROR_CODE_FAIL, '一周只能提现一次哦'];
        }

        if ($user->getWithdrawAmount() < $amount) {
            return [ERROR_CODE_FAIL, '提现金额超过可提现最大值'];
        }

        $history = new WithdrawHistories();
        $history->user_id = $user->id;
        $history->user_name = $user->nickname;
        $history->product_channel_id = $user->product_channel_id;
        $history->amount = $amount;
        $history->status = WITHDRAW_STATUS_WAIT;
        $history->type = WITHDRAW_TYPE_USER;
        $history->withdraw_account_id = $withdraw_account_id;
        $history->withdraw_account_type = $withdraw_account->type;
        $history->account = $withdraw_account->account;
        $history->mobile = $withdraw_account->mobile;

        if ($history->save()) {
            HiCoinHistories::createHistory($user->id, ['withdraw_history_id' => $history->id]);
            return [ERROR_CODE_SUCCESS, '受理中'];
        }

        return [ERROR_CODE_FAIL, '提现失败'];
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

    function toPushDataJson()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user_nickname,
            'alipay_account' => $this->alipay_account,
            'product_channel_id' => $this->product_channel_id,
            'product_channel_name' => $this->product_channel_name,
            'amount' => $this->amount,
            'type' => $this->type_text,
            'status' => $this->status_text,
            'created_at' => $this->created_at
        ];
    }

    static function waitWithdrawHistory($user)
    {
        $start = beginOfWeek();
        $end = endOfWeek();

        if (isDevelopmentEnv()) {
            $start = time() - 120;
            $end = time();
        }

        $withdraw_history = WithdrawHistories::findFirst(
            [
                'conditions' => '(user_id = :user_id: and type = :type: and created_at >= :start: and created_at <= :end: and ' .
                    'status != :status1:) or (status = :status: and user_id = :user_id1:)',
                'bind' => ['user_id' => $user->id, 'type' => WITHDRAW_TYPE_USER, 'start' => $start, 'end' => $end,
                    'status' => WITHDRAW_STATUS_WAIT, 'user_id1' => $user->id, 'status1' => WITHDRAW_STATUS_FAIL],
                'order' => 'id desc'
            ]
        );

        return $withdraw_history;
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
        $titles = ['日期', '用户id', '姓名', '账户', '账户类型', '提现金额'];
        $data = [];
        foreach ($withdraw_histories as $withdraw_history) {

            $account = $withdraw_history->alipay_account ? $withdraw_history->alipay_account : $withdraw_history->account;

            if (mb_strlen($withdraw_history->user_name) < 2) {
                $data[] = [$withdraw_history->created_at_text, $withdraw_history->user_id, '', $account, $withdraw_history->withdraw_account_type_text,
                    $withdraw_history->amount];
            } else {
                $data[] = [$withdraw_history->created_at_text, $withdraw_history->user_id, $withdraw_history->user_name, $account, $withdraw_history->withdraw_account_type_text,
                    $withdraw_history->amount];
            }
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

    static function findLastWithdrawHistory($user_id)
    {
        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id],
            'order' => 'id desc'
        ];

        $withdraw_history = WithdrawHistories::findFirst($conditions);

        return $withdraw_history;
    }

    function getWithdrawAccountTypeText()
    {
        return fetch(WithdrawAccounts::$TYPE, $this->withdraw_account_type);
    }

    function getAccountText()
    {
        if (!$this->account) {
            return '';
        }
        $arr = str_split($this->account, 4);
        $str = implode(' ', $arr);
        return $str;
    }
}
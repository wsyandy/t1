共{{ withdraw_histories.total_entries }}条记录

{{ simple_table(withdraw_histories, [
    '日期': 'created_at_text',"ID": 'id', "用户ID": 'user_id', "支付宝账号": 'alipay_account','用户昵称': 'user_name','提现金额':'amount','提现状态': 'status_text'
]) }}
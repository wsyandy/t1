共{{ account_histories.total_entries }}条记录
<br/>
{% if isAllowed('account_histories', 'give_diamond') %}
    <a href="/admin/account_histories/give_diamond?user_id={{ user_id }}" class="modal_action">赠送钻石</a>
{% endif %}
{% if isAllowed('orders', 'manual_recharge') %}
    <a href="/admin/orders/manual_recharge?user_id={{ user_id }}" class="modal_action">人工充值</a>
{% endif %}

{%- macro user_link(object) %}
    <a href="/admin/users/detail?id={{ object.user_id }}"><img src="{{ object.user.avatar_url }}" width="30"></a>
{%- endmacro %}


{{ simple_table(account_histories, [
    'ID': 'id', '用户': 'user_link', '类型': 'fee_type_text', '金额(钻石)': 'amount',
    '余额(钻石)': 'balance','target_id':'target_id','备注': 'remark', '时间': 'created_at_text'
]) }}

共{{ i_gold_histories.total_entries }}条记录
<br/>
<a href="/admin/i_gold_histories/give_i_gold?user_id={{ user_id }}" class="modal_action">赠送国际版金币</a>


{%- macro user_link(object) %}
    <a href="/admin/users/detail?id={{ object.user_id }}"><img src="{{ object.user.avatar_url }}" width="30"></a>
{%- endmacro %}

{%- macro order_link(object) %}
    {% if object.order_id %}
        <a href="/admin/orders/{{ object.order_id }}">订单</a>
    {% endif %}
{%- endmacro %}

{%- macro gift_order_link(object) %}
    {% if object.gift_order_id %}
        <a href="/admin/gift_orders/show/{{ object.gift_order_id }}">礼物订单</a>
    {% endif %}
{%- endmacro %}

{{ simple_table(i_gold_histories, [
    'ID': 'id', '用户': 'user_link', '类型': 'fee_type_text', '金额(国际版金币)': 'amount',
    '账户余额(国际版金币)': 'balance', '订单':'order_link', '礼物订单': 'gift_order_link',
    '备注': 'remark', '时间': 'created_at_text'
]) }}

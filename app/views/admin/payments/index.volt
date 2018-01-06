{%- macro user_link(object) %}
    <a href="/admin/users/detail?user_id={{ object.user_id }}"><img src="{{ object.user.avatar_small_url }}" width="30"></a>
{%- endmacro %}

{%- macro order_link(object) %}
    <a href="/admin/orders?id={{ object.order_id }}">订单</a>
{%- endmacro %}

{{ simple_table(payments, [
    'ID': 'id', '流水号': 'payment_no', '用户': 'user_link', '订单': 'order_link',
    '支付通道': 'payment_channel_name', '支付类型': 'payment_type', '金额': 'amount',
    '支付状态': 'pay_status_text', '创建时间': 'created_at_text'
]) }}
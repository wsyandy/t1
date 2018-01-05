{%- macro sender_link(object)  %}
    <a href="/admin/users/{{ object.sender_id }}"><img src="{{ object.sender.avatar_small_url }}" width="30"></a>
{%- endmacro %}

{%- macro user_link(object)  %}
    <a href="/admin/users/{{ object.user_id }}"><img src="{{ object.user.avatar_small_url }}" width="30"></a>
{%- endmacro %}

{{ simple_table(gift_orders, [
    'ID': 'id', '礼物名称': 'name', '礼物个数': 'gift_num',
    '支付金额': 'amount', '发送方': 'sender_link',
    '接收方': 'user_link', '支付状态': 'status_text', '备注': 'remark'
]) }}
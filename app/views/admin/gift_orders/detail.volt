{%- macro sender_link(object) %}
    ID:<a href="/admin/users?user[id_eq]={{ object.sender_id }}">{{ object.sender_id }}</a><br/>
    姓名:{{ object.sender_nickname }}<br/>
    手机号码:{{ object.sender_mobile }}<br/>
    用户类型:{{ object.sender_user_type_text }}
{%- endmacro %}

{%- macro user_link(object) %}
    ID:<a href="/admin/users?user[id_eq]={{ object.user_id }}">{{ object.user_id }}</a><br/>
    姓名:{{ object.user.nickname }}<br/>
    手机号码:{{ object.user.mobile }}<br/>
    用户类型:{{ object.receiver_user_type_text }}
{%- endmacro %}

{{ simple_table(gift_orders, [
    '创建时间':'created_at_text','ID': 'id', '礼物名称': 'name', '礼物个数': 'gift_num',
    '支付金额': 'amount', '发送方': 'sender_link',
    '接收方': 'user_link', '支付状态': 'status_text', '备注': 'remark'
]) }}
{%- macro user_link(object) %}
    <a href="/admin/users/detail?id={{ object.user_id }}"><img src="{{ object.user.avatar_small_url }}" width="30"></a>
{%- endmacro %}

{%- macro order_link(object) %}
    <a href="/admin/orders?id={{ object.order_id }}">订单</a>
{%- endmacro %}

{%- macro pay_status_link(object) %}
    <a href="/admin/payments/pay_status?id={{ object.id }}" class="modal_action">{{ object.pay_status_text }}</a>
{%- endmacro %}

{{ simple_table(payments, [
    'ID': 'id', '流水号': 'payment_no', '用户': 'user_link', '订单': 'order_link',
    '支付通道': 'payment_channel_name', '支付类型': 'payment_type', '金额': 'amount',
    '支付状态': 'pay_status_link', '创建时间': 'created_at_text'
]) }}

<script type="text/template" id="payment_tpl">
<tr id="payment_${payment.id}">
    <td>${payment.id}</td>
    <td>${payment.payment_no}</td>
    <td><a href="/admin/users/detail?id=${payment.user_id}"><img src="${payment.user_avatar_url}" width="30"></a></td>
    <td><a href="/admin/orders?id=${payment.order_id}">订单</a></td>
    <td>${payment.payment_channel_name}</td>
    <td>${payment.payment_type}</td>
    <td>${payment.amount}</td>
    <td><a href="/admin/payments/pay_status?id=${payment.id}" class="modal_action">${payment.pay_status_text}</a></td>
    <td>${payment.created_at_text}</td>
</tr>
</script>
{%- macro user_link(object)  %}
    <a href="/admin/users/detail?user_id={{ object.user_id }}"><img src="{{ object.user.avatar_small_url }}" width="30"></a>
{%- endmacro %}

{%- macro payments_link(object) %}
    <a href="/admin/payments?order_id={{ object.id }}">支付流水</a>
{%- endmacro %}

{{ simple_table(orders, [
    "ID": 'id', '用户': 'user_link', '产品': 'product_name',
    '金额': 'amount','支付状态': 'status_text',
    '支付流水': 'payments_link', '时间': 'created_at_text'
]) }}
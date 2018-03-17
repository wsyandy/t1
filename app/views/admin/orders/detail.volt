
{%- macro user_link(object) %}
    <a href="/admin/users/detail?id={{ object.user_id }}"><img src="{{ object.user.avatar_small_url }}" width="50"></a>
{%- endmacro %}

{%- macro payments_link(object) %}
    {% if isAllowed('payments','index') %}
        <a href="/admin/payments?order_id={{ object.id }}">支付流水</a>
    {% endif %}
{%- endmacro %}

{% macro user_info(object) %}
    ID:<a href="/admin/users/detail?id={{ object.user_id }}">{{ object.user_id }}</a><br>
    姓名:{{ object.user.nickname }} <br/>
    性别:{{ object.user.sex_text }}<br/>
    手机号码:{{ object.user.mobile }}<br/>
{% endmacro %}

{{ simple_table(orders, [
    "ID": 'id', '用户头像': 'user_link','用户信息':'user_info','产品': 'product_name',
    '金额': 'amount','支付状态': 'status_text',
    '支付流水': 'payments_link', '时间': 'created_at_text'
]) }}
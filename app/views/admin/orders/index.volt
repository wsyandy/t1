<form action="/admin/orders" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="order[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels,'','id','name') }}
    </select>

    <label for="id_eq">ID</label>
    <input name="order[id_eq]" type="text" id="id_eq"/>

    <label for="user_id_eq">用户ID</label>
    <input name="order[user_id_eq]" type="text" id="user_id_eq"/>

    <label for="mobile">手机号</label>
    <input name="order[mobile_eq]" type="text" id="mobile"/>

    <button type="submit" class="ui button">搜索</button>
</form>

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
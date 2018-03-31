<form action="/admin/orders/hi_coin_histories" method="get" class="search_form" autocomplete="off" id="search_form">

    <label for="user_id_eq">用户ID</label>
    <input name="user_id" type="text" id="user_id_eq"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{%- macro user_link(object) %}
    <a href="/admin/users/detail?id={{ object.user_id }}"><img src="{{ object.user.avatar_small_url }}" width="50"></a>
{%- endmacro %}


{% macro user_info(object) %}
    ID:<a href="/admin/users/detail?id={{ object.user_id }}">{{ object.user_id }}</a><br>
    姓名:{{ object.user.nickname }} <br/>
    性别:{{ object.user.sex_text }}<br/>
    手机号码:{{ object.user.mobile }}<br/>
{% endmacro %}

{{ simple_table(hi_coin_histories, [
    "ID": 'id', '用户头像': 'user_link','用户信息':'user_info','产品': 'product_name','Hi币': 'hi_coins','时间': 'created_at_text'
]) }}
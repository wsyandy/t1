<form method="get" action="/admin/users/day_rank_list" name="search_form" autocomplete="off">

    <label for="year">榜单类型</label>
    <select name="type" id="type">
        {{ options(types,type) }}
    </select>

    <button class="ui button" type="submit">搜索</button>
</form>

{% macro avatar_image(user) %}
    <img src="{{ user.avatar_small_url }}" height="50"/>
{% endmacro %}

{% macro user_info(user) %}
    姓名:{{ user.nickname }}  性别:{{ user.sex_text }} 段位:{{ user.segment_text }}<br/>
    魅力值:{{ user.charm_value }} 财富值:{{ user.wealth_value }}<br/>
{% endmacro %}

{{ simple_table(users,['用户id': 'id','头像': 'avatar_image', '用户信息':'user_info']) }}
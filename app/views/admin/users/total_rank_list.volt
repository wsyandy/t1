<form method="get" action="/admin/users/total_rank_list" name="search_form" autocomplete="off">

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
    {% if user.charm %}
        魅力值:{{ user.charm }}<br/>
    {% endif %}
    {% if user.wealth %}
        财富值:{{ user.wealth }}<br/>
    {% endif %}
{% endmacro %}

{{ simple_table(users,['用户id': 'id','头像': 'avatar_image', '用户信息':'user_info']) }}
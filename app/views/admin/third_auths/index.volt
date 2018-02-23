{% macro user_info(third_auth) %}
    用户名: {{ third_auth.user_nickname }}<br/>
    用户id: {{ third_auth.user_id }}
{% endmacro %}

{% macro delete_link(third_auth) %}
    <a href="/admin/third_auths/delete/{{ third_auth.id }}" class="delete_action"
       data-target="#third_auth_{{ third_auth.id }}">删除</a>
{% endmacro %}
{{ simple_table(third_auths, [
    'ID': 'id',
    '渠道名称': 'product_channel_name',
    '用户信息': 'user_info',
    '登录方式': 'third_name_text',
    '第一次登录时间': 'created_at_text',
    '最近一次登录时间': 'login_at_text',
    '删除': 'delete_link'
]) }}
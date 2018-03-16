{% macro oper_link(union) %}
    {% if isAllowed('users','index')  %}
        <a href="/admin/users/index?user[union_id_eq]={{ union.id }}" >家族成员</a><br/>
    {% endif %}
{% endmacro %}

{% macro avatar_img(union) %}
    <img src="{{ union.avatar_small_url }}" height="20%">
{% endmacro %}

{% macro family_info(union) %}
    声望：{{ union.fame_value }} 公告：{{ union.notice }}<br/>
    家族设置：{{ union.need_apply_text }} 家族成员数：{{ union.user_num }}<br/>
{% endmacro %}

{{ simple_table(unions, ['ID': 'id',"头像":"avatar_img",'家族名称': 'name','用户': 'user_nickname','家族信息':'family_info',
'状态': 'status_text','操作' :'oper_link'
]) }}
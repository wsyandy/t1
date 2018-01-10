{%- macro avatar_link(user) %}
    <img src="{{ user.avatar_url }}" height="50" width="50"/>
{%- endmacro %}
{{ simple_table(users,['用户id': 'id','头像': 'avatar_link','用户名称':'nickname','性别':'sex_text']) }}
{%- macro avatar_link(user) %}
    <img src="{{ user.avatar_url }}" height="50" width="50"/>
{%- endmacro %}
{{ simple_table(users,['好友id': 'id','头像': 'avatar_link','好友名称':'nickname','性别':'sex_text','好友状态':'friend_status_text']) }}
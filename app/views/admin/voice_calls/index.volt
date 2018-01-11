共{{ voice_calls.total_entries }}条记录

{%- macro user_link(object) %}
    <a href="/admin/users/detail?id={{ object.user.id }}"><img src="{{ object.user_avatar_url }}" width="30"></a>
{%- endmacro %}

{{ simple_table(voice_calls, [
    'ID': 'id', '通话用户': 'user_link', '昵称': 'user_nickname', '通话状态': 'call_status',
    '状态描述': 'call_status_text', '通话时长': 'duration', '时间': 'created_at_text'
]) }}
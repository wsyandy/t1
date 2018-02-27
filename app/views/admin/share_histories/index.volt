共{{ share_histories.total_entries }}条记录
<br/>

{%- macro user_link(object) %}
    <a href="/admin/users/detail?id={{ object.user_id }}"><img src="{{ object.user.avatar_url }}" width="30"></a>
{%- endmacro %}

{{ simple_table(share_histories, [
'ID': 'id', '用户': 'user_link', '来源':'share_source','类型': 'type_text','状态':'status_text','链接点击次数': 'view_num', '时间': 'created_at_text'
]) }}
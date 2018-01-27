{% macro complainer_link(complaint) %}
    <a href="/admin/users?user[id_eq]={{ complaint.complainer_id }}">{{ complaint.complainer_nickname }}</a>
{% endmacro %}

{% macro respondent_link(complaint) %}
    <a href="/admin/users?user[id_eq]={{ complaint.respondent_id }}">{{ complaint.respondent_nickname }}</a>
{% endmacro %}

{% macro room_link(complaint) %}
    <a href="/admin/rooms?room[id_eq]={{ complaint.room_id }}">{{ complaint.room_name }}</a>
{% endmacro %}

{{ simple_table(complaints, [
'ID': 'id', '时间': 'created_at_text', '举报人':'complainer_link','被举报人': 'respondent_link',
'房间': 'room_link','举报类型': 'complaint_type_text',
'状态': 'status_text'
]
) }}


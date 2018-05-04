{% macro avatar_img(union) %}
    <a href="/admin/unions/family?id={{ union.id }}"><img src="{{ union.avatar_small_url }}" height="50"/></a>
{% endmacro %}
{% macro operation_link(union) %}
    {% if isAllowed('unions','rooms') %}
        <a href="/admin/unions/rooms?id={{ union.id }}">厅流水统计</a>
    {% endif %}
    {% if isAllowed('unions','users_rank') %}
        <a href="/admin/unions/users_rank?id={{ union.id }}">家族成员流水统计</a>
    {% endif %}
{% endmacro %}
{{ simple_table(unions, ['ID': 'id',"头像":"avatar_img",'声望':'fame_value','操作':'operation_link']) }}

{% macro avatar_img(union) %}
    <a href="/admin/unions/family?id={{ union.id }}"><img src="{{ union.avatar_small_url }}" height="50"/></a>
{% endmacro %}

{{ simple_table(unions, ['ID': 'id',"头像":"avatar_img",'声望':'fame_value']) }}

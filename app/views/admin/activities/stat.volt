{% macro image_link(activity) %}
    <img src="{{ activity.image_small_url }}" alt="" height="50"/>
{% endmacro %}

{%- macro stat_detail_link(activity) %}
    <a class="modal_action" href="/admin/activities/{{ activity.code }}?id={{ activity.id }}">查看</a>
{%- endmacro %}

<form name="search_form" action="/admin/activities/stat" method="get" autocomplete="off" id="search_form">
    <label for="id">ID</label>
    <input name="activity[id_eq]" type="text" id="id"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{{ simple_table(activities, ['id': 'id','标题': 'title','图片': 'image_link','状态':'status_text','code':'code',
'明细':'stat_detail_link'
]) }}


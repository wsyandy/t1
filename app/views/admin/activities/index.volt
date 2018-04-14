<a href="/admin/activities/new" class="modal_action">新建</a>

{% macro image_link(activity) %}
    <img src="{{ activity.image_small_url }}" alt="" height="50"/>
{% endmacro %}

{%- macro platforms_link(activity) %}
    <a class="modal_action"
       href="/admin/activities/platforms/{{ activity.id }}">查看({{ activity.platform_num }})</a>
{%- endmacro %}

{%- macro product_channel_link(activity) %}
    <a class="modal_action" href="/admin/activities/product_channel_ids?id={{ activity.id }}">查看({{ activity.product_channel_num }})</a>
{%- endmacro %}

<form name="search_form" action="/admin/activities" method="get" autocomplete="off" id="search_form">
    <label for="id">ID</label>
    <input name="activity[id_eq]" type="text" id="id"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{{ simple_table(activities, ['id': 'id','标题': 'title','图片': 'image_link','状态':'status_text', '排序':'rank','code':'code',
'开始时间':'start_at_text','结束时间':'end_at_text','支持的平台':'platforms_link','产品渠道':'product_channel_link','编辑': 'edit_link']) }}

<script type="text/template" id="activity_tpl">
    <tr id="activity_${activity.id}">
        <td>${activity.id}</td>
        <td>${activity.title}</td>
        <td><img src="${activity.image_small_url}" alt="" height="50"></td>
        <td>${activity.status_text}</td>
        <td>${activity.rank}</td>
        <td>${activity.code}</td>
        <td>${activity.start_at_text}</td>
        <td>${activity.end_at_text}</td>
        <td><a class="modal_action" href="/admin/activities/platforms?id=${activity.id}">查看</a></td>
        <td><a class="modal_action" href="/admin/activities/product_channel_ids?id=${activity.id}">查看</a></td>
        <td><a href="/admin/activities/edit/${activity.id}" class="modal_action">编辑</a></td>
    </tr>
</script>
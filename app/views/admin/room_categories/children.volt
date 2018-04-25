<a href="/admin/room_categories/new?parent_id={{ parent_id }}" class="modal_action">新建</a>

<form name="search_form" action="/admin/room_categories/children" method="get" autocomplete="off" id="search_form">
    <label for="id">ID</label>
    <input name="room_category[id_eq]" type="text" id="id"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{% macro image_link(object) %}
    <img src="{{ object.image_url }}" width="50"/>
{% endmacro %}

{{ simple_table(room_categories, ['id': 'id','名称': 'name','图片':'image_link','类型': 'type','状态':'status_text','排序':'rank','编辑': 'edit_link']) }}

<script type="text/template" id="room_category_tpl">
    <tr id="room_category_${room_category.id}">
        <td>${room_category.id}</td>
        <td>${room_category.name}</td>
        <td><img src="${ room_category.image_url }" width="50"/></td>
        <td>${room_category.type}</td>
        <td>${room_category.status_text}</td>
        <td>${room_category.rank}</td>
        <td><a href="/admin/room_categories/edit/${room_category.id}" class="modal_action">编辑</a></td>
    </tr>
</script>
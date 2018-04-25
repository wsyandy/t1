<a href="/admin/room_tags/new" class="modal_action">新建</a>

{%- macro operator_link(object) %}
    <a href="/admin/room_tags/edit?id={{ object.id }}" class="modal_action">编辑</a><br/>
{%- endmacro %}

<form name="search_form" action="/admin/room_tags" method="get" autocomplete="off" id="search_form">
    <label for="id">ID</label>
    <input name="room_tag[id_eq]" type="text" id="id"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{{ simple_table(room_tags, ['id': 'id','名称': 'name','状态':'status_text', '排序':'rank','操作': 'operator_link']) }}

<script type="text/template" id="room_tag_tpl">
    <tr id="room_tag_${room_tag.id}">
        <td>${room_tag.id}</td>
        <td>${room_tag.name}</td>
        <td>${room_tag.status_text}</td>
        <td>${room_tag.rank}</td>
            <a href="/admin/room_tags/edit/${room_tag.id}" class="modal_action">编辑</a>
        </td>
    </tr>
</script>
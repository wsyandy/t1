<a href="/admin/room_category_keywords/new?room_category_id={{ room_category_id }}" class="modal_action">新建</a>

{{ simple_table(room_category_keywords, ['id': 'id','名称': 'name', '编辑':'edit_link', '删除':'delete_link']) }}

<script type="text/template" id="room_category_keyword_tpl">
    <tr id="room_category_keyword_${ room_category_keyword.id }">
        <td>${room_category_keyword.id}</td>
        <td>${room_category_keyword.name}</td>
    </tr>
</script>
<a href="/admin/room_themes/new" class="modal_action">新增</a>

{%- macro image_link(room_theme) %}
    <img src="{{ room_theme.image_url }}" width="50"/>
{%- endmacro %}

{%- macro icon_link(room_theme) %}
    <img src="{{ room_theme.icon_url }}" width="50"/>
{%- endmacro %}

{%- macro edit_link(room_theme) %}
    <a href="/admin/room_themes/edit/{{ room_theme.id }}" class="modal_action">编辑</a>
{%- endmacro %}

共{{ room_themes.total_entries }}个

{{ simple_table(room_themes, [
"ID": 'id', "名称": 'name',"背景图": 'image_link',"Icon": 'icon_link',
"有效": 'status_text', "排序":'rank', '编辑': 'edit_link'
]) }}

<script type="text/template" id="room_theme_tpl">
    <tr id="room_theme_${ room_theme.id }">
        <td>${room_theme.id}</td>
        <td>${room_theme.name}</td>
        <td><img src="${room_theme.image_url}" width="30"></td>
        <td><img src="${room_theme.icon_url}" width="30"></td>
        <td>${room_theme.status_text}</td>
        <td>${room_theme.rank}</td>
        <td><a href="/admin/room_themes/edit/${room_theme.id}" class="modal_action">编辑</a></td>
    </tr>
</script>
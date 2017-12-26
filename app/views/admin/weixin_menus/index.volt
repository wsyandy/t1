<h4>{{ weixin_menu_template_name }}的一级菜单</h4>

<a href="/admin/weixin_menus/new?weixin_menu_template_id={{ weixin_menu_template_id }}" class="modal_action">新建菜单</a>

{% macro sub_menu_link(weixin_menu) %}
    <a href="/admin/weixin_sub_menus/{{ weixin_menu.id }}">子菜单列表</a>
{% endmacro %}

{{ simple_table(weixin_menus,['id':'id','菜单名称':'name','菜单类型':'type','菜单url':'url','排序':'rank','子菜单列表':'sub_menu_link','修改':'edit_link','删除':'delete_link']) }}

<script type="text/template" id="weixin_menu_tpl">
    <tr id="weixin_menu_${weixin_menu.id}">
        <td>${weixin_menu.id}</td>
        <td>${weixin_menu.name}</td>
        <td>${weixin_menu.type}</td>
        <td>${weixin_menu.url}</td>
        <td>${weixin_menu.rank}</td>
        <td><a href="/admin/weixin_sub_menus/${weixin_menu.id}">子菜单列表</a></td>
        <td><a href="/admin/weixin_menus/edit/${weixin_menu.id}" class="modal_action">修改</a></td>
        <td><a href="/admin/weixin_menus/delete/${weixin_menu.id}" data-target="#weixin_menu_${weixin_menu.id}" class="delete_action">删除</a></td>
    </tr>
</script>
<h4>{{ weixin_menu_name }}的子菜单</h4>

<a href="/admin/weixin_sub_menus/new?weixin_menu_id={{ weixin_menu_id }}" class="modal_action">新建子菜单</a>

{{ simple_table(weixin_sub_menus,['id':'id','菜单名称':'name','菜单类型':'type','菜单url':'url','排序':'rank','修改':'edit_link','删除':'delete_link']) }}

<script type="text/template" id="weixin_sub_menu_tpl">
    <tr id="weixin_sub_menu_${weixin_sub_menu.id}">
        <td>${weixin_sub_menu.id}</td>
        <td>${weixin_sub_menu.name}</td>
        <td>${weixin_sub_menu.type}</td>
        <td>${weixin_sub_menu.url}</td>
        <td>${weixin_sub_menu.rank}</td>
        <td><a href="/admin/weixin_sub_menus/edit/${weixin_sub_menu.id}" class="modal_action">修改</a></td>
        <td><a href="/admin/weixin_sub_menus/delete/${weixin_sub_menu.id}" data-target="#weixin_sub_menu_${weixin_sub_menu.id}" class="delete_action">删除</a></td>
    </tr>
</script>
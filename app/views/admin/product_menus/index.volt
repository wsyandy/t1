<a href="/admin/product_menus/new" class="modal_action">新建</a>

<form name="search_form" action="/admin/product_menus" method="get" autocomplete="off" id="search_form">
    <label for="id">ID</label>
    <input name="product_menu[id_eq]" type="text" id="id"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{{ simple_table(product_menus, ['id': 'id','名称': 'name','类型': 'type','状态':'status_text', '排序':'rank','编辑': 'edit_link']) }}

<script type="text/template" id="product_menu_tpl">
    <tr id="product_menu_${product_menu.id}">
        <td>${product_menu.id}</td>
        <td>${product_menu.name}</td>
        <td>${product_menu.type}</td>
        <td>${product_menu.status_text}</td>
        <td>${product_menu.rank}</td>
        <td><a href="/admin/product_menus/edit/${product_menu.id}" class="modal_action">编辑</a></td>
    </tr>
</script>
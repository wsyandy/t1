<h4>菜单模板</h4>

<a href="/admin/weixin_menu_templates/new" class="modal_action">新建模板</a>

{% macro menu_link(weixin_menu_template) %}
    <a href="/admin/weixin_menus?weixin_menu_template_id={{ weixin_menu_template.id }}">菜单列表</a>
{% endmacro %}

{% macro menu_template_link(weixin_menu_template) %}
    <a href="/admin/weixin_menu_templates/product_channel_list?weixin_menu_template_id={{ weixin_menu_template.id }}" class="modal_action">查看</a>
{% endmacro %}

{{ simple_table(weixin_menu_templates,['id':'id','模板名称':'name','支持的产品渠道':'menu_template_link','菜单列表':'menu_link','修改':'edit_link','删除':'delete_link']) }}

<script type="text/template" id="weixin_menu_template_tpl">
    <tr id="weixin_menu_template_${weixin_menu_template.id}">
        <td>${weixin_menu_template.id}</td>
        <td>${weixin_menu_template.name}</td>
        <td>
            <a href="/admin/weixin_menu_templates/product_channel_list?weixin_menu_template_id=${weixin_menu_template.id}" class="modal_action">查看</a></td>
        <td><a href="/admin/weixin_menus?weixin_menu_template_id=${weixin_menu_template.id}">菜单列表</a></td>
        <td><a href="/admin/weixin_menu_templates/edit/${weixin_menu_template.id}" class="modal_action">修改</a></td>
        <td><a href="/admin/weixin_menu_templates/delete/${weixin_menu_template.id}"
               data-target="#weixin_menu_template_${weixin_menu_template.id}" class="delete_action">删除</a></td>
    </tr>
</script>
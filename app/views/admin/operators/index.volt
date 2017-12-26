{%- macro partners_link(operator) %}
    <a class="modal_action" href="/admin/operators/partners/{{ operator.id }}">配置推广渠道</a>
{%- endmacro %}

{% if isAllowed('operators','new') %}
    <a href="/admin/operators/new" class="modal_action">新建</a>
{% endif %}

{%- macro new_edit_link(operator) %}
    {% if isAllowed('operators','edit') %}
        <a class="modal_action" href="/admin/operators/edit/{{ operator.id }}">编辑</a>
    {% endif %}
{%- endmacro %}

{{ simple_table(operators, ['id':'id','用户名': 'username','状态': 'status_text','角色名称':'role_text','角色':'role',
'查看渠道':'partners_link','编辑':'new_edit_link']) }}

<script type="text/template" id="operator_tpl">
    <tr id="operator_${operator.id}">
        <td>${operator.id}</td>
        <td>${operator.username}</td>
        <td>${operator.status_text}</td>
        <td>${operator.role_text}</td>
        <td>${operator.role}</td>
        <td><a class="modal_action" href="/admin/operators/partners/${operator.id}">配置推广渠道</a></td>
        <td><a href="/admin/operators/edit/${operator.id}" class="modal_action">编辑</a></td>
    </tr>
</script>
<a href="/admin/product_groups/new?product_channel_id={{ product_channel_id }}" class="modal_action">新增</a>
<a href="/admin/product_channels">返回</a>

{%- macro icon_link(object) %}
    <img src="{{ object.icon_url }}" width="40">
{%- endmacro %}

{%- macro edit_link(object) %}
    <a href="/admin/product_groups/edit/{{ object.id }}" class="modal_action">编辑</a>
{%- endmacro %}

{%- macro product_channel_link(object) %}
    <a href="/admin/product_channels">{{ object.product_channel.name }}</a>
{%- endmacro %}

{%- macro products_link(object) %}
    <a href="/admin/products?product_group_id={{ object.id }}&product_channel_id={{ object.product_channel_id }}">产品配置</a>
{%- endmacro %}

{{ simple_table(product_groups, [
    'ID': 'id', '产品渠道': 'product_channel_link', '名称': 'name', '产品类型': 'fee_type_text',
    '支付类型': 'pay_type_text','产品配置': 'products_link', 'icon': 'icon_link', '备注': 'remark',
    '状态': 'status_text', '编辑': 'edit_link'
]) }}

<script type="text/template" id="product_group_tpl">
    <tr id="product_group_${product_group.id}">
        <td>${product_group.id}</td>
        <td>${product_group.product_channel_name}</td>
        <td>${product_group.name}</td>
        <td>${product_group.fee_type_text}</td>
        <td>${product_group.pay_type_text}</td>
        <td>
            <a href="/admin/products?product_group_id=${product_group.id}&product_channel_id=${product_group.product_channel_id}">产品配置</a>
        </td>
        <td><img src="${product_group.icon_url}" width="40"></td>
        <td>${product_group.remark}</td>
        <td>${product_group.status_text}</td>
        <td><a href="/admin/product_groups/edit/${ product_group.id }" class="modal_action">编辑</a></td>
    </tr>
</script>


<script type="text/javascript">
    $(function () {
        {% for product_group in product_groups %}
        {% if product_group.status != 1 %}
        $("#product_group_{{ product_group.id }}").css({"background-color": "grey"});
        {% endif %}
        {% endfor %}
    });
</script>
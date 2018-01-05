<a href="/admin/products/new?product_group_id={{ product_group_id }}" class="modal_action">新增</a>
<a href="/admin/product_groups?product_channel_id={{ product_channel_id }}">返回</a>

{%- macro icon_link(object)  %}
    <img src="{{ object.icon_url }}" width="40" />
{%- endmacro %}

{%- macro edit_link(object) %}
    <a href="/admin/products/edit/{{ object.id }}" class="modal_action">编辑</a>
{%- endmacro  %}

{{ simple_table(products, [
    'ID': 'id', '产品组': 'product_group_name', '名称': 'name', 'icon': 'icon_link', '金额(元)': 'amount',
    '钻石': 'diamond', '排序': 'rank', '状态': 'status_text', '编辑': 'edit_link'
]) }}

<script type="text/template" id="product_tpl">
  <tr id="product_${product.id}">
      <td>${product.id}</td>
      <td>${product.product_group_name}</td>
      <td>${product.name}</td>
      <td><img src="${product.icon_url}" width="40"></td>
      <td>${product.amount}</td>
      <td>${product.diamond}</td>
      <td>${product.rank}</td>
      <td>${product.status_text}</td>
      <td><a href="/admin/products/edit/${product.id}" class="modal_action">编辑</a></td>
  </tr>
</script>
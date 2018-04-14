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
    '国际版金币':'i_gold','钻石': 'diamond', '金币':'gold','Hi币':'hi_coins','苹果支付代码': 'apple_product_no','谷歌支付代码': 'google_product_no', '排序': 'rank', '状态': 'status_text', '编辑': 'edit_link'
]) }}

<script type="text/template" id="product_tpl">
  <tr id="product_${product.id}">
      <td>${product.id}</td>
      <td>${product.product_group_name}</td>
      <td>${product.name}</td>
      <td><img src="${product.icon_url}" width="40"></td>
      <td>${product.amount}</td>
      <td>${product.i_gold}</td>
      <td>${product.diamond}</td>
      <td>${product.gold}</td>
      <td>${product.hi_coins}</td>
      <td>${product.apple_product_no}</td>
      <td>${product.google_product_no}</td>
      <td>${product.rank}</td>
      <td>${product.status_text}</td>
      <td><a href="/admin/products/edit/${product.id}" class="modal_action">编辑</a></td>
  </tr>
</script>

<script type="text/javascript">
    $(function () {

        {% for product in products %}
        {% if product.status != 1 %}
        $("#product_{{ product.id }}").css({"background-color": "grey"});
        {% endif %}
        {% endfor %}
    });
</script>
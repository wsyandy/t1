<a href="/admin/payment_channels/new" class="modal_action">新增</a>

{%- macro edit_link(object) %}
    <a href="/admin/payment_channels/edit/{{ object.id }}" class="modal_action">编辑</a>
{%- endmacro  %}

{%- macro product_channel_link(object) %}
    <a href="/admin/payment_channels/product_channels?payment_channel_id={{ object.id }}" class="modal_action">产品渠道</a>
{%- endmacro %}

{{ simple_table(payment_channels, [
    'ID': 'id', '名称': 'name', '商户名称': 'mer_name', '产品渠道': 'product_channel_link', '状态': 'status_text', '编辑': 'edit_link'
]) }}

<script type="text/template" id="payment_channel_tpl">
<tr id="payment_channel_${payment_channel.id}">
    <td>${payment_channel.id}</td>
    <td>${payment_channel.name}</td>
    <td>${payment_channel.mer_name}</td>
    <td><a href="/admin/payment_channels/product_channels?payment_channel_id=${payment_channel.id}" class="modal_action">产品渠道</a></td>
    <td>${payment_channel.status_text}</td>
    <td><a href="/admin/payment_channels/edit/${payment_channel.id}" class="modal_action">编辑</a></td>
</tr>
</script>
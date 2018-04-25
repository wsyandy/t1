
<a class="modal_action" href="/admin/partner_accounts/new_configs/{{ partner_account_id }}">新建</a>

{{ simple_table(partner_account_product_channels, ['ID': 'id', '产品渠道':'product_channel_name', '推广渠道':'partner_name',
'合作方账号': 'partner_account_username', '删除':'delete_link']) }}

<script type="text/template" id="partner_account_product_channel_tpl">
    <tr id="partner_account_product_channel_${partner_account_product_channel.id}">
        <td>${partner_account_product_channel.id}</td>
        <td>${partner_account_product_channel.product_channel_name}</td>
        <td>${partner_account_product_channel.partner_name}</td>
        <td>${partner_account_product_channel.partner_account_username}</td>
    </tr>
</script>

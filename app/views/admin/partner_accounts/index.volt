{% if isAllowed('partner_accounts','new') %}
    <a href="https://pd.momoyuedu.cn/admin/partner_accounts/new" class="modal_action">新建</a> <a target="_blank" href="{{ root }}pt">登陆后台</a>
{% endif %}

{%- macro new_edit_link(partner_account) %}
    {% if isAllowed('partner_accounts','edit') %}
        <a class="modal_action" href="/admin/partner_accounts/edit/{{ partner_account.id }}">编辑</a>
    {% endif %}
{%- endmacro %}

{%- macro config_link(partner_account) %}
    <a class="" href="/admin/partner_accounts/configs/{{ partner_account.id }}">双渠道配置</a>
{%- endmacro %}

{{ simple_table(partner_accounts, ['id':'id','用户名': 'username','状态': 'status_text',
'配置':'config_link','编辑':'new_edit_link']) }}

<script type="text/template" id="partner_account_tpl">
    <tr id="partner_account_${partner_account.id}">
        <td>${partner_account.id}</td>
        <td>${partner_account.username}</td>
        <td>${partner_account.status_text}</td>
        <td><a class="" href="/admin/partner_accounts/configs//${partner_account.id}">双渠道配置</a></td>
        <td><a href="/admin/partner_accounts/edit/${partner_account.id}" class="modal_action">编辑</a></td>
    </tr>
</script>
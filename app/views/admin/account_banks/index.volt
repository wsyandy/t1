<a href="/admin/account_banks/new" class="modal_action">新建</a>
{% macro icon_link(account_bank) %}
    <img src="{{ account_bank.icon_small_url }}" alt="" height="80"/>
{% endmacro %}
{{ simple_table(account_banks, ['id': 'id', '名称': 'name', 'ICON': 'icon_link','code':'code','排序':'rank','状态':'status_text', '编辑': 'edit_link']) }}

<script type="text/template" id="account_bank_tpl">
    <tr id="account_bank_${account_bank.id}">
        <td>${account_bank.id}</td>
        <td>${account_bank.name}</td>
        <td><img src="${account_bank.icon_small_url}" alt="" height="80"></td>
        <td>${account_bank.code}</td>
        <td>${account_bank.rank}</td>
        <td>${account_bank.status_text}</td>
        <td><a href="/admin/account_banks/edit/${account_bank.id}" class="modal_action">编辑</a></td>
    </tr>
</script>
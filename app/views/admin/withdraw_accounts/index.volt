

{%- macro area(withdraw_account) %}
        {{ withdraw_account.province_name }},{{ withdraw_account.city_name }}
{%- endmacro %}


{{ simple_table(withdraw_accounts, [
'日期': 'created_at_text',"ID": 'id',"用户ID":'user_id','用户昵称': 'user_name',"账户": 'account','账户类型':'type_text','手机号': 'mobile',
'收款银行':'account_bank_name','收款支行':'bank_account_location','地区':'area','状态': 'status_text'
]) }}

<script type="text/template" id="withdraw_account_tpl">
    <tr id="withdraw_account_${ withdraw_account.id }">
        <td>${withdraw_account.created_at_text}</td>
        <td>${withdraw_account.id}</td>
        <td>${withdraw_account.user_id}</td>
        <td>${withdraw_account.account}</td>
        <td>${withdraw_account.type_text}</td>
        <td>${withdraw_account.status_text}</td>
    </tr>
</script>


<script type="text/javascript">
    $(".form_datetime").datetimepicker({
        language: "zh-CN",
        format: 'yyyy-mm-dd',
        autoclose: 1,
        todayBtn: 1,
        todayHighlight: 1,
        startView: 2,
        minView: "month"
    });
    $('.selectpicker').selectpicker();
</script>
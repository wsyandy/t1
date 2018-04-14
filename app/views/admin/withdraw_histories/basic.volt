共{{ withdraw_histories.total_entries }}条记录

{%- macro account_link(withdraw_histories) %}
    {% if(withdraw_histories.account ) %}
        {{ withdraw_histories.account_text }}
    {% else %}
        {{ withdraw_histories.alipay_account }}
    {% endif %}
{%- endmacro %}

{{ simple_table(withdraw_histories, [
'日期': 'created_at_text',"ID": 'id', "用户ID": 'user_id', "账号": 'account_link','账号类型':'withdraw_account_type_text','用户昵称': 'user_name','提现金额':'amount','提现状态': 'status_text'
]) }}
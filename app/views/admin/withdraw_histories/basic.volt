共{{ withdraw_histories.total_entries }}条记录

{%- macro account_link(withdraw_histories) %}
    {% if(withdraw_histories.account ) %}
        {{ withdraw_histories.account_text }}
    {% else %}
        {{ withdraw_histories.alipay_account }}
    {% endif %}
{%- endmacro %}

{%- macro area(withdraw_hitories) %}
    {% if(withdraw_hitories.withdraw_account) %}
        {{ withdraw_hitories.withdraw_account.province_name }},{{ withdraw_hitories.withdraw_account.city_name }}
    {% endif %}
{%- endmacro %}

{%- macro account_bank_name(withdraw_hitories) %}
    {% if(withdraw_hitories.withdraw_account) %}
        {{ withdraw_hitories.withdraw_account.account_bank.name }}
    {% endif %}
{%- endmacro %}


{{ simple_table(withdraw_histories, [
'日期': 'created_at_text',"ID": 'id', "用户ID": 'user_id', "账户": 'account_link','账户类型':'withdraw_account_type_text',
'用户昵称': 'user_name','收款银行':'account_bank_name','收款支行':'withdraw_account_bank_account_location','地区':'area',
'提现金额':'amount','提现状态': 'status_text'
]) }}
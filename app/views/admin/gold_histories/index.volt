共{{ gold_histories.total_entries }}条记录
<br/>

<a href="/admin/gold_histories/give_gold?user_id={{ user_id }}" class="modal_action">赠送金币</a>

{%- macro user_link(object) %}
    <a href="/admin/users/detail?id={{ object.user_id }}"><img src="{{ object.user.avatar_url }}" width="30"></a>
{%- endmacro %}

{{ simple_table(gold_histories, [
    'ID': 'id', '用户': 'user_link', '类型': 'fee_type_text', '金币': 'amount',
    '余额(金币)': 'balance','target_id':'target_id','备注': 'remark', '时间': 'created_at_text'
]) }}

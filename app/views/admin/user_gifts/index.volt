{%- macro user_link(user_gift)  %}
    <a href="/admin/users/detail?id={{ user_gift.user_id }}"><img src="{{ user_gift.user.avatar_small_url }}" width="30"></a>
{%- endmacro  %}
{{ simple_table(user_gifts, [
    'ID': 'id', '用户': 'user_link', '礼物名称': 'name', '礼物个数': 'num',
    '支付类型':'pay_type_text', '礼物类型':'gift_type_text',
    '礼物金额': 'amount', '总金额': 'total_amount', '时间':'created_at_text'
]) }}
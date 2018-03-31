共{{ hi_coin_histories.total_entries }}条记录

<br/>
<a href="/admin/hi_coin_histories/create_hi_coins?user_id={{ user_id }}" class="modal_action">房间流水奖励</a>

{{ simple_table(hi_coin_histories, [
    'ID': 'id', '昵称': 'user_nickname', '简介': 'remark','余额': 'balance', 'hi币': 'hi_coins',
    '类型':'fee_type_text', '礼物订单id':'gift_order_id', '计费产品id':'product_id', '时间': 'created_at_text'
]) }}

<script type="text/template" id="hi_coin_history_tpl">
    <tr id="hi_coin_history_${hi_coin_history.id}">
        <td>${hi_coin_history.id}</td>
        <td>${hi_coin_history.user_nickname}</td>
        <td>${hi_coin_history.remark}</td>
        <td>${hi_coin_history.balance}</td>
        <td>${hi_coin_history.hi_coins}</td>
        <td>${hi_coin_history.fee_type_text}</td>
        <td>${hi_coin_history.gift_order_id}</td>
        <td>${hi_coin_history.created_at_text}</td>
    </tr>
</script>

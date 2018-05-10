<ol class="breadcrumb">
    <li class="active">奖池预留率: {{ pool_rate }}</li>
    <li class="active">钻石总收入: {{ total_incr_num }}</li>
    <li class="active">钻石总支出: {{ total_decr_num }}</li>
    <li class="active">礼物钻石总支出: {{ total_decr_gift_num }}</li>
    <li class="active">金币总支出: {{ total_decr_gold_num }}</li>
</ol>

<form action="/admin/draw_histories" method="get" class="search_form" autocomplete="off" id="search_form">

    <label for="user_id_eq">用户ID</label>
    <input name="draw_history[user_id_eq]" type="text" id="user_id_eq"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{{ simple_table(draw_histories,[
'id': 'id','user_id':'user_id', '支付类型':'pay_type_text', '支付金额':'pay_amount','获奖类型':'type_text',
'获奖数量':'number','支付总钻石':'total_pay_amount','获得总金币':'total_gold','获得总钻石':'total_diamond',
'获得礼物钻石':'total_gift_diamond','获得礼物数':'total_gift_num',
'创建时间': 'created_at_text']) }}
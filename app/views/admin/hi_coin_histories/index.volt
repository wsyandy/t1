<form action="/admin/hi_coin_histories/index" method="get" class="search_form" autocomplete="off" id="search_form">

    <label for="user_uid_eq">用户UID</label>
    <input name="user_uid" type="text" id="user_uid_eq" value="{{ user_uid }}"/>

    <label for="union_id_eq">家族ID</label>
    <input name="union_id" type="text" id="union_id_eq" value="{{ union_id }}"/>

    <label for="fee_type">类型</label>
    <select name="fee_type" id="fee_type" class="selectpicker" data-live-search="true">
        {{ options(HiCoinHistories.FEE_TYPE, fee_type) }}
    </select>

    <label for="start_at">开始时间</label>
    <input type="text" name="start_at" class="form_datetime" id="start_at" value="{{ start_at }}" size="16">

    <label for="end_at">结束时间</label>
    <input type="text" name="end_at" class="form_datetime" id="end_at" value="{{ end_at }}" size="16">

    <button type="submit" class="ui button">搜索</button>
</form>

总计{{ total_hi_coins }}

{{ simple_table(hi_coin_histories, [
    'ID': 'id', '用户UID': 'user_uid', '家族ID':'union_id', '简介': 'remark','余额': 'balance', 'hi币': 'hi_coins',
    '类型':'fee_type_text', '礼物订单id':'gift_order_id', '计费产品id':'product_id', '时间': 'created_at_text'
]) }}

<script type="text/javascript">
    $(function () {
        $('.selectpicker').selectpicker();

        $(".form_datetime").datetimepicker({
            language: "zh-CN",
            format: 'yyyy-mm-dd',
            autoclose: 1,
            todayBtn: 1,
            todayHighlight: 1,
            startView: 2,
            minView: 2
        });

        {% for product_channel in product_channels %}
        {% if product_channel.status != 1 %}
        $("#product_channel_{{ product_channel.id }}").css({"background-color": "grey"});
        {% endif %}
        {% endfor %}
    });
</script>
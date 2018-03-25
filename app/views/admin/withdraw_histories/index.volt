<form action="/admin/withdraw_histories" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="withdraw_history[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels,'','id','name') }}
    </select>

    <label for="id_eq">ID</label>
    <input name="withdraw_history[id_eq]" type="text" id="id_eq" value="{{ id }}"/>

    <label for="user_id_eq">用户ID</label>
    <input name="user_id" type="text" id="user_id_eq" value="{{ user_id }}"/>

    <label for="status_eq">提现状态</label>
    <select name="withdraw_history[status_eq]" id="status_eq">
        {{ options(WithdrawHistories.STATUS, status) }}
    </select>

    <label for="user_name_eq">用户昵称</label>
    <input name="withdraw_history[user_name_eq]" type="text" id="user_name_eq" value="{{ user_name }}"/>

    <label for="start_at_eq">开始时间</label>
    <input name="start_at" type="text" id="start_at_eq" class="form_datetime" value="{{ start_at }}"/>
    <label for="end_at_eq">结束时间</label>
    <input name="end_at" type="text" id="end_at_eq" class="form_datetime" value="{{ end_at }}"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{% if isAllowed('withdraw_histories', 'export') %}
    <form action="/admin/withdraw_histories/export" target="_blank" method="get" class="search_form"
          autocomplete="off">

        <label for="start_at">开始时间</label>
        <input type="text" name="start_at" class="form_datetime" id="start_at" value="{{ start_at }}" size="16">

        <label for="end_at">结束时间</label>
        <input type="text" name="end_at" class="form_datetime" id="end_at" value="{{ end_at }}" size="16">


        <label for="status_eq">提现状态</label>
        <select name="status" id="status_eq">
            {{ options(WithdrawHistories.STATUS, status) }}
        </select>

        <button type="submit" class="ui button">导出</button>
    </form>
{% endif %}

{%- macro oper_link(withdraw_histories) %}
    {% if(withdraw_histories.status == 0) %}
        <a href="/admin/withdraw_histories/edit/{{ withdraw_histories.id }}" class="modal_action">编辑</a>
    {% endif %}
{%- endmacro %}
{{ simple_table(withdraw_histories, [
'日期': 'created_at_text',"ID": 'id', "用户ID": 'user_id', "支付宝账号": 'alipay_account','用户昵称': 'user_name','提现金额':'amount','提现状态': 'status_text','操作': 'oper_link'
]) }}

<script type="text/template" id="withdraw_history_tpl">
    <tr id="withdraw_history_${ withdraw_history.id }">
        <td>${withdraw_history.created_at_text}</td>
        <td>${withdraw_history.id}</td>
        <td>${withdraw_history.user_id}</td>
        <td>${withdraw_history.alipay_account}</td>
        <td>${withdraw_history.user_name}</td>
        <td>${withdraw_history.amount}</td>
        <td>${withdraw_history.status_text}</td>
        <td><a href="/admin/withdraw_histories/edit/${withdraw_history.id}">编辑</a></td>
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

<script type="text/javascript">
    $(function () {
        {% for withdraw_history in withdraw_histories %}
        {% if withdraw_history.status == 2 %}
        $("#withdraw_history_{{ withdraw_history.id }}").css({"background-color": "grey"});
        {% endif %}
        {% endfor %}
    });
</script>
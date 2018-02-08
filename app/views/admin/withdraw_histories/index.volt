<form action="/admin/withdraw_histories" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="withdraw_history[product_channel_id_eq]" id="product_channel_id_eq">
        {{ options(product_channels,'','id','name') }}
    </select>

    <label for="id_eq">ID</label>
    <input name="withdraw_history[id_eq]" type="text" id="id_eq"/>

    <label for="user_name_eq">用户昵称</label>
    <input name="withdraw_history[user_name_eq]" type="text" id="user_name_eq"/>

    <label for="start_at_eq">开始时间</label>
    <input name="start_at" type="text" id="start_at_eq" class="form_datetime" value="{{ start_at }}"/>
    <label for="end_at_eq">结束时间</label>
    <input name="end_at" type="text" id="end_at_eq" class="form_datetime" value="{{ end_at }}"/>

    <button type="submit" class="ui button">搜索</button>
</form>
{%- macro oper_link(withdraw_histories) %}
    {% if(withdraw_histories.status == 0) %}
        <a href="/admin/withdraw_histories/edit/{{ withdraw_histories.id }}" class="modal_action">编辑</a>
    {% endif %}
{%- endmacro %}
{{ simple_table(withdraw_histories, [
'日期': 'created_at_text',"ID": 'id', "用户ID": 'user_id','用户昵称': 'user_name','提现金额':'amount','提现状态': 'status_text','操作': 'oper_link'
]) }}

<script type="text/template" id="withdraw_history_tpl">
    <tr id="withdraw_history_${ withdraw_history.id }">
        <td>${withdraw_history.created_at_text}</td>
        <td>${withdraw_history.id}</td>
        <td>${withdraw_history.user_id}</td>
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
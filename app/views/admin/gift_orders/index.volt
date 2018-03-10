<form action="/admin/gift_orders" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="id_eq">ID</label>
    <input name="gift_order[id_eq]" type="text" id="id_eq"/>

    <label for="sender_id_eq">发送方ID</label>
    <input name="gift_order[sender_id_eq]" type="text" id="sender_id_eq"/>

    <label for-="user_id_eq">接收方ID</label>
    <input name="gift_order[user_id_eq]" type="text" id="user_id_eq"/>

    <label for="start_at_eq">开始时间</label>
    <input name="start_at" type="text" id="start_at_eq" class="form_datetime" value="{{ start_at }}"/>
    <label for="end_at_eq">结束时间</label>
    <input name="end_at" type="text" id="end_at_eq" class="form_datetime" value="{{ end_at }}"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{%- macro sender_link(object) %}
    ID:<a href="/admin/users?user[id_eq]={{ object.sender_id }}">{{ object.sender_id }}</a><br/>
    姓名:{{ object.sender_nickname }}<br/>
    手机号码:{{ object.sender_mobile }}<br/>
    用户类型:{{ object.sender_user_type_text }}
{%- endmacro %}

{%- macro user_link(object) %}
    ID:<a href="/admin/users?user[id_eq]={{ object.user_id }}">{{ object.user_id }}</a><br/>
    姓名:{{ object.user.nickname }}<br/>
    手机号码:{{ object.user.mobile }}<br/>
    用户类型:{{ object.receiver_user_type_text }}
{%- endmacro %}

{{ simple_table(gift_orders, [
    '创建时间':'created_at_text','ID': 'id', '礼物名称': 'name', '礼物个数': 'gift_num',
    '支付金额': 'amount', '发送方': 'sender_link',
    '接收方': 'user_link', '支付状态': 'status_text', '备注': 'remark'
]) }}


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
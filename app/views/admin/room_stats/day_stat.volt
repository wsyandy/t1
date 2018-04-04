<form method="get" action="/admin/room_stats/day_stat" name="search_form" autocomplete="off">
    <label for="start_date">开始时间</label>
    <input type="text" name="start_date" class="form_datetime" id="start_date" value="{{ start_date }}" size="16">

    <label for="end_date">结束时间</label>
    <input type="text" name="end_date" class="form_datetime" id="end_date" value="{{ end_date }}" size="16">

    <label for="user_id">房主ID</label>
    <input type="text" name="user_id" id="user_id" value="{{ user_id }}">

    <label for="room_id">房间ID</label>
    <input type="text" name="room_id" id="room_id" value="{{ room_id }}">

    <label for="union_id">家族ID</label>
    <input type="text" name="union_id" id="union_id" value="{{ union_id }}">

    <button class="ui button" type="submit">搜索</button>
</form>

{% macro user_info(room) %}
    用户ID: {{ room.user_id }}<br/>
    {% if isAllowed('users','index') %}
        姓名:<a href="/admin/users?user[id_eq]={{ room.user_id }}">{{ room.user_nickname }}</a><br/>
    {% endif %}
{% endmacro %}

{{ simple_table(rooms, [
    '房间ID': 'id', '名称': 'name', '房主信息':"user_info",'进入房间人数':'total_enter_room_user','钻石流水':'total_income','送钻石礼物人数':'total_send_gift_user',
    '送钻石礼物个数':'total_send_gift_num','人均送钻石礼物个数':'total_send_gift_average_num','房主时长':'total_host_broadcaster_time_text',
    '主播时长':'total_broadcaster_time_text','旁听时长':'total_audience_time_text'
]) }}

<script type="text/javascript">


    $(function () {
        $(".form_datetime").datetimepicker({
            language: "zh-CN",
            format: 'yyyy-mm-dd',
            autoclose: 1,
            todayBtn: 1,
            todayHighlight: 1,
            startView: 2,
            minView: 2
        })
    });
</script>
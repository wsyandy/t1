<form method="get" action="/admin/room_stats/day_stat" name="search_form" autocomplete="off">
    <label for="date">时间</label>
    <input type="text" name="date" class="form_datetime" id="date" value="{{ date }}" size="16">

    <button class="ui button" type="submit">搜索</button>
</form>

{% macro user_info(room) %}
    用户ID: {{ room.user_id }}
    {% if isAllowed('users','index') %}
        姓名:<a href="/admin/users?user[id_eq]={{ room.user_id }}">{{ room.user_nickname }}</a><br/>
    {% endif %}
{% endmacro %}

{{ simple_table(rooms, [
    '房间ID': 'id', '名称': 'name', '房主信息':"user_info", '流水':'day_income','进入房间人数':'day_enter_room_user','送礼物人数':'day_send_gift_user',
    '房主时长':'day_host_broadcaster_time','主播时长':'day_broadcaster_time','旁听时长':'day_audience_time'
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
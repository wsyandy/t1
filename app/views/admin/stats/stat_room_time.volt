<form method="get" action="/admin/stats/stat_room_time" name="search_form" autocomplete="off">
    <label for="stat_at">时间</label>
    <input type="text" name="stat_at" class="form_datetime" id="stat_at" value="{{ stat_at }}" size="16">

    <label for="user_id">用户ID</label>
    <input type="text" name="user_id" id="user_id" value="{{ user_id }}">

    <button class="ui button" type="submit">搜索</button>
</form>

{{ simple_table(users, ['ID': 'id', '昵称': 'nickname','房主时长':'host_broadcaster_time','主播时长':'broadcaster_time',
    '旁听时长':'audience_time']) }}

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
        });
    });
</script>
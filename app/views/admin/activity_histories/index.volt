<form method="get" action="/admin/activity_histories" name="search_form" autocomplete="off">

    <label for="stat_at">时间</label>
    <input type="text" name="stat_at" class="form_datetime" id="stat_at" value="{{ stat_at }}" size="16">

    <label for="platform">
        奖品类型
    </label>
    <select name="prize_type" id="prize_type">
        {{ options(activity_prize_types, prize_type) }}
    </select>


    <input type="hidden" name="activity_id" value="{{ activity_id }}"/>
    <button class="ui button" type="submit">搜索</button>
</form>

<ol class="breadcrumb">
    <li class="active">总个数 {{ total_num }}</li>
</ol>

{{ simple_table(activity_histories, [
    "ID": 'id', "用户ID": 'user_id', '奖励类型':'prize_type_text', '状态':'auth_status_text','日期': 'created_at_text'
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
        });
    });
</script>
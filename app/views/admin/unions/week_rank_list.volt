<form action="/admin/unions/week_rank_list" method="get" class="search_form" autocomplete="off" id="search_form">

    <label for="start_at_eq">开始时间</label>
    <input name="start_at" type="text" id="start_at_eq" class="form_datetime" value="{{ start_at }}"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{% macro avatar_img(union) %}
    <a href="/admin/unions/family?id={{ union.id }}"><img src="{{ union.avatar_small_url }}" height="50"/></a>
{% endmacro %}

{{ simple_table(unions, ['ID': 'id',"头像":"avatar_img",'排名':'rank','声望':'fame_value']) }}

<script type="text/javascript">
    $(function () {
        $(".form_datetime").datetimepicker({
            language: "zh-CN",
            format: 'yyyy-mm-dd',
            weekStart: 1,
            autoclose: 1,
            todayBtn: 1,
            todayHighlight: 1,
            startView: 2,
            minView: 2,
            daysOfWeekDisabled: [0, 2, 3, 4, 5, 6, 7]
        });
    });
</script>
<form action="/admin/unions/users_rank" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="start_at_time_eq">开始时间</label>
    <input name="start_at_time" type="text" id="start_at_time_eq" class="form_datetime" value="{{ start_at_time }}"/>

    <label for="end_at_time_eq">结束时间</label>
    <input name="end_at_time" type="text" id="end_at_time_eq" class="form_datetime" value="{{ end_at_time }}"/>
    <input type="hidden" name="id" id="id_eq" value="{{ id }}">
    <button type="submit" class="ui button">搜索</button>
</form>
<label for="stat_at_eq">魅力值累计{{ total_charm }}</label>
<label for="stat_at_eq">贡献值累计{{ total_wealth }}</label>
<label for="stat_at_eq">hi币收益累计{{ total_hi_coins }}</label>

{%- macro icon_link(user) %}
    <img src="{{ user.avatar_small_url }}" height="50" width="50"/>
{%- endmacro %}
{{ simple_table(users, ['成员信息': 'nickname','家族id':'union_id','头像':'icon_link','魅力值':'charm','贡献值':'wealth',"hi币收益":"hi_coins"]) }}


<script type="text/javascript">
    // $('.selectpicker').selectpicker();

    $(".form_datetime").datetimepicker({
        language: "zh-CN",
        format: 'yyyy-mm-dd',
        autoclose: 1,
        todayBtn: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2
    });
</script>
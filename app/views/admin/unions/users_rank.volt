<form action="/admin/unions/users_rank" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="start_at_time_eq">开始时间</label>
    <input name="start_at_time" type="text" id="start_at_time_eq" class="form_datetime" value="{{ start_at_time }}"/>

    <label for="end_at_time_eq">结束时间</label>
    <input name="end_at_time" type="text" id="end_at_time_eq" class="form_datetime" value="{{ end_at_time }}"/>
    <input type="hidden" name="id" id="id_eq" value="{{ id }}">
    <button type="submit" class="ui button">搜索</button>
</form>
<label for="stat_at_eq">累计{{ total_hi_coins }}</label>

{{ simple_table(users, ['成员信息': 'nickname_link','魅力值':'charm_value','贡献值':'wealth_value',"hi币收益":"hi_coins"]) }}

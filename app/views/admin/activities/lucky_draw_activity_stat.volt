<form method="get" action="/admin/activities/lucky_draw_activity_stat" name="search_form" autocomplete="off">
    <label for="year">年份</label>
    <select name="year" id="year">
        {{ options(year_array,year) }}
    </select>
    <label for="month">月份</label>
    <select name="month" id="month">
        {{ options(Stats.MONTH,month) }}
    </select>

    <input type="hidden" name="id" value="{{ activity_id }}">
    <button class="ui button" type="submit">搜索</button>
</form>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>时间</th>
        <th>获得抽奖人数</th>
        <th>获得抽奖次数</th>
        <th>抽奖人数</th>
        <th>抽奖次数</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for day, result in results %}
        <tr id="{{ day }}" class="row_line">
            <td>{{ day }}</td>
            <td>{{ result['obtain_day_user'] }}</td>
            <td>{{ result['obtain_day_num'] }}</td>
            <td>{{ result['day_user'] }}</td>
            <td>{{ result['day_num'] }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>


<script type="text/javascript">

    $(function () {
        $('.selectpicker').selectpicker();
    });

</script>
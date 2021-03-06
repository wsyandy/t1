<form method="get" action="/admin/room_stats/total_stat_detail" name="search_form" autocomplete="off">
    <label for="year">年份</label>
    <select name="year" id="year">
        {{ options(year_array,year) }}
    </select>
    <label for="month">月份</label>
    <select name="month" id="month">
        {{ options(Stats.MONTH,month) }}
    </select>

    <input type="hidden" name="id" value="{{ room_id }}">
    <button class="ui button" type="submit">搜索</button>
</form>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>时间</th>
        <th>收益</th>
        <th>明细</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for day, result in results %}
        <tr id="{{ day }}" class="row_line">
            <td>{{ day }}</td>
            <td>{{ result }}钻石</td>
            <td>
                <a href="/admin/gift_orders?gift_order[room_id_eq]={{ room_id }}&status=1&start_at={{ day }}&end_at={{ day }}">明细</a>
            </td>
        </tr>
    {% endfor %}
    </tbody>
</table>


<script type="text/javascript">

    $(function () {
        $('.selectpicker').selectpicker();
    });

</script>
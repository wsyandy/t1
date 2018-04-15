{#<form method="get" action="/admin/gift_stats/days" name="search_form" autocomplete="off">#}
    {##}
    {#<label for="stat_at">时间</label>#}
    {#<input name="stat_at" type="text" id="stat_at" class="form_datetime" value="{{ stat_at }}"/>#}

    {#<button class="ui button" type="submit">搜索</button>#}
{#</form>#}


<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>时间</th>
        <th>发送人数</th>
        <th>活跃人数</th>
        <th>充值人数</th>
        <th>充值金额</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for data in datas %}
        <tr class="row_line">
            <td>{{ data['stat_at'] }}</td>
            <td>{{ data['send_user'] }}</td>
            <td>{{ data['active_user'] }}</td>
            <td>{{ data['recharge_user'] }}</td>
            <td>{{ data['recharge_amount'] }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>


<script type="text/javascript">


</script>

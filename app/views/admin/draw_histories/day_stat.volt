<form action="/admin/draw_histories/day_stat" method="get" class="search_form" autocomplete="off" id="search_form">

    <label for="stat_at_eq">时间</label>
    <input name="stat_at" type="text" id="stat_at_eq" class="form_datetime" value="{{ stat_at }}"/>

    <button type="submit" class="ui button">搜索</button>
</form>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>砸蛋钻石数</th>
        <th>中奖钻石数</th>
        <th>中奖金币数</th>
        <th>中奖座驾数</th>
        <th>砸蛋次数</th>
        <th>砸蛋人数</th>
        <th>砸蛋次数</th>
        <th>人均砸蛋次数</th>
        <th>人均砸蛋钻石</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for key, val in stats %}
        <tr id="{{ key }}" class="row_line">
            <td>{{ val }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>

<script type="text/javascript">
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
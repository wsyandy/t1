<form action="/admin/draw_histories/hour_stat" method="get" class="search_form" autocomplete="off" id="search_form">

    <label for="stat_at_eq">时间</label>
    <input name="stat_at" type="text" id="stat_at_eq" class="form_datetime" value="{{ stat_at }}"/>

    <button type="submit" class="ui button">搜索</button>
</form>

<table class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>时间</th>
        <th>砸蛋钻石数</th>
        <th>中奖钻石数</th>
        <th>中奖金币数</th>
        <th>中奖座驾数</th>
        <th>砸蛋次数</th>
        <th>砸蛋人数</th>
        <th>人均砸蛋次数</th>
        <th>人均砸蛋钻石</th>
    </tr>
    </thead>

    <tbody id="stat_list">

    {% for day, val in stats %}
    <tr id="{{ day }}" class="row_line">
        <th>{{ day }}</th>
        <th>{{ val['total_pay_amount'] }}</th>
        <th>{{ val['total_diamond'] }}</th>
        <th>{{ val['total_gold'] }}</th>
        <th>{{ val['total_gift_num'] }}</th>
        <th>{{ val['total_hit_num'] }}</th>
        <th>{{ val['total_hit_user_num'] }}</th>
        <th>{{ val['avg_hit_num'] }}</th>
        <th>{{ val['avg_hit_diamond'] }}</th>
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
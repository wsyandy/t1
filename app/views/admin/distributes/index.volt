<div id="app">
    <form action="/admin/distributes" method="get" class="search_form" autocomplete="off" id="search_form">

        <label for="stat_at_eq">时间</label>
        <input name="stat_at" type="text" id="stat_at_eq" class="form_datetime" value="{{ stat_at }}"/>

        <button type="submit" class="ui button">搜索</button>
    </form>

    <table class="table table-striped table-condensed">
        <thead>
        <tr>
            <th>时间</th>
            <th>分享次数</th>
            <th>分享人数</th>
            <th>人均分享次数</th>
            <th>已邀请人数</th>
            <th>邀请注册的钻石奖励</th>
            <th>一级充值分成的钻石奖励</th>
            <th>二级充值分成的钻石奖励</th>
            <th>总钻石奖励</th>
        </tr>
        </thead>

        <tbody id="stat_list">
        {% for day,data in datas %}
            <tr id="{{ day }}" class="row_line">
                <th>{{ day }}</th>
                <th>{{ data['share_num'] }}</th>
                <th>{{ data['share_distribute_user_num'] }}</th>
                <th>{{ data['per_capita_share_num'] }}</th>
                <th>{{ data['invited_user_num'] }}</th>
                <th>{{ data['share_register_bonus'] }}</th>
                <th>{{ data['first_distribute_bonus'] }}</th>
                <th>{{ data['second_distribute_bonus'] }}</th>
                <th>{{ data['distribute_total_amount'] }}</th>
            </tr>
        {% endfor %}
        </tbody>
    </table>
</div>
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
{{ block_begin('head') }}
{{ theme_css('/partner/css/jedate-select.css', '/partner/css/jedate.css') }}
{{ theme_js('/partner/css/jquery.jedate.min.js', '/partner/css/jedate-select.js') }}
{{ block_end() }}

<form method="post" action="/partner/private_unions/rooms">
    <div class="search">

        <div class="form-group">
            <label class="search_label">开始时间</label>
            <input type="text" class="input search_input" value="{{ start_at_time }}" id="time_tart"
                   name="start_at_time"
                   placeholder="起始时间"/>
            <label class="search_label">结束时间</label>
            <input type="text" class="input search_input" value="{{ end_at_time }}" id="time_end" name="end_at_time"
                   placeholder="起始时间"/>
        </div>
        <div class="form-group ">
            <div class="field padding-large-left">
                <button class="button bg-dot icon-search " type="submit"> 搜索</button>
            </div>

        </div>

    </div>
</form>

<div class="admin-panel padding">
    <table class="table table-hover  tab-account">
        <thead>
        <tr>
            {#<th width="20%">日期</th>#}
            <th width="20%">房间名称</th>
            <th width="20%">房主ID</th>
            <th width="20%">房主名称</th>
            <th>金额</th>

        </tr>
        </thead>
        <tbody>
        {% for room in rooms %}
            <tr>
                {#<td>{{ stat_at }}</td>#}
                <td>{{ room.name }}</td>
                <td>{{ room.user.uid }}</td>
                <td>{{ room.user.nickname }}</td>
                <td>{{ room.amount }}</td>
            </tr>
        {% endfor %}
        </tbody>


        <tfoot>
        <tr class="cumulative">
            <td>累计</td>
            {#<td></td>#}
            <td colspan="2">{{ total_amount }}</td>
        </tr>
        </tfoot>
    </table>
</div>

<script type="text/javascript">

    var opts = {
        data: {},
        methods: {}
    };

    vm = XVue(opts);


</script>

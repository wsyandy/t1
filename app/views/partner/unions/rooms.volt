{{ block_begin('head') }}
{{ theme_css('/partner/css/jedate-select.css', '/partner/css/jedate.css') }}
{{ theme_js('/partner/css/jquery.jedate.min.js', '/partner/css/jedate-select.js') }}
{{ block_end() }}

<form method="post" action="/partner/unions/rooms">
    <div class="search">

        <div class="form-group">
            <label class="search_label">时间</label>
            <input type="text" class="input search_input" value="{{ stat_at }}" id="timestart" name="stat_at"
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
            <th width="20%">日期</th>
            <th width="20%">项目</th>
            <th>金额</th>

        </tr>
        </thead>
        <tbody>
        {% for room in rooms %}
            <tr>
                <td>{{ stat_at }}</td>
                <td>流水</td>
                <td>{{ room.amount }}</td>
            </tr>
        {% endfor %}
        </tbody>


        <tfoot>
        <tr class="cumulative">
            <td>累计</td>
            <td></td>
            <td colspan="2">{{ total_amount }}</td>
        </tr>
        </tfoot>
    </table>
</div>

<script type="text/javascript">

    var opts = {
        data: {
            agreement: true,
            upload_status: false
        },
        methods: {}
    };

    vm = XVue(opts);


</script>

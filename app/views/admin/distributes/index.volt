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
        <th>已邀请人数</th>
        <th>邀请的钻石奖励</th>
        <th>一级充值分成的钻石奖励</th>
        <th>二级充值分成的钻石奖励</th>
        <th>总钻石奖励</th>
    </tr>
    </thead>

    <tbody id="stat_list">
    {% for data in datas %}
        <tr id="" class="row_line">
            <th>{{ data['invited_user_num'] }}</th>
            <th>{{ data['share_num'] }}</th>
            <th>{{ data['invited_user_num'] }}</th>
            <th>{{ data['invited_user_num'] }}</th>
            <th>{{ data['invited_user_num'] }}</th>
            <th>{{ data['invited_user_num'] }}</th>
            <th>{{ data['invited_user_num'] }}</th>
        </tr>

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

//    var opts = {
//        data: {
//            distributes: []
//        },
//        created: function () {
//            this.getDistributes();
//        },
//        methods: {
//            getDistributes: function () {
//                $.authPost('/admin/distributes', '', function (resp) {
//                    console.log(resp);
//                    if (!resp.error_code && resp.distributes) {
//                        $.each(resp.distributes, function (index, distribute) {
//                            vm.distributes.push(distribute);
//                        });
//                        console.log(vm.distributes);
//                    }
//                })
//            }
//        }
//    };
//
//    vm = XVue(opts);
//    $(function () {
//        $(document).scroll(
//            function () {
//                if ($(document).scrollTop() + window.innerHeight == $(document).height()) {
//                    vm.getDistributes()
//                }
//            });
//    })
</script>
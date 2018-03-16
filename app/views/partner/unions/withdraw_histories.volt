{{ block_begin('head') }}
{{ theme_css('/partner/css/jedate-select.css', '/partner/css/jedate.css') }}
{{ theme_js('/partner/css/jquery.jedate.min.js', '/partner/css/jedate-select.js') }}
{{ block_end() }}
<form method="post" action="">
    <div class="search">
        <div class="form-group ">
            <label class="search_label">待结算金额：</label>
            <span class="search_span">
                  {{ union.amount }}（元）
              </span>

        </div>
        <div class="form-group ">
            <label class="search_label">已结算金额：</label>
            <span class="search_span">
                   {{ union.settled_amount }}（元）
              </span>

        </div>

    </div>
    <div class="admin-panel padding" v-if="total_page > 0">
        <table class="table table-hover  ">
            <thead>
            <tr>
                <th>结算金额（元）</th>
                <th>结算状态</th>
                <th>结算日期</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="withdraw_history in datas">

                <td>${withdraw_history.amount}</td>
                <td>${withdraw_history.status_text}</td>
                <td>${withdraw_history.created_at_text}</td>
            </tr>
            </tbody>


            <tfoot v-if="total_page > 1">
            <tr>
                <td colspan="8">
                    <div class="pagelist">
                        <span @click.stop="firstPage(1)" v-if="current_page > 1">首页</span>
                        <span @click.stop="prePage()" v-if="current_page > 1">上一页</span>

                        <span v-for="page in (1, total_page)" :class="{current:page == current_page}"
                              @click="jumpPage(page)" v-if="page < current_page + 5 && page + 5 > current_page">
                            ${page}</span>

                        <span @click.stop="nextPage()" v-if="current_page < total_page">下一页</span>
                        <span @click.stop="lastPage(total_page)" v-if="current_page < total_page">尾页</span>
                    </div>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</form>
<script type="text/javascript">

    var opts = {
        data: {
            datas: [],
            current_page: 1,
            total_entries: 0,
            total_page: 1
        },
        methods: {
            firstPage: function (page) {

                if (vm.current_page == page) {
                    return;
                }

                vm.current_page = 1;
                loadData();
            },
            lastPage: function (page) {

                if (vm.current_page == page) {
                    return;
                }

                vm.current_page = vm.total_page;
                loadData();
            },
            nextPage: function () {

                if (vm.current_page >= vm.total_page) {
                    return;
                }

                vm.current_page++;
                loadData();
            },
            prePage: function () {

                if (vm.current_page <= 1) {
                    return;
                }

                vm.current_page--;
                loadData();
            },
            jumpPage: function (page) {
                if (vm.current_page == page) {
                    return;
                }
                vm.current_page = page;
                loadData();
            }
        }
    };

    vm = XVue(opts);

    function loadData() {

        if (vm.current_page > vm.total_page || vm.current_page < 1) {
            return;
        }

        $.authGet('/partner/unions/withdraw_histories',
            {
                page: vm.current_page,
            }, function (resp) {
                var withdraw_histories = resp.withdraw_histories;
                vm.datas = [];
                $.each(withdraw_histories, function (index, item) {
                    vm.datas.push(item);
                    vm.total_page = resp.total_page;
                })

            })
    }

    loadData();

</script>
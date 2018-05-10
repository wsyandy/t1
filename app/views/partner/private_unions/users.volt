<form method="post" action="/partner/private_unions/users">
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
    <table class="table table-hover">

        <tr class="cumulative">
            <td>累计</td>
            <td></td>
            <td></td>
            <td colspan="2">{{ total_hi_coins }}</td>
        </tr>

        <thead>
        <tr>
            <th>成员信息</th>
            <th>魅力值</th>
            <th>贡献值</th>
            <th>hi币收益</th>
        </tr>
        </thead>
        <tbody>

        <tr v-for="user in datas">
            <td class="flexbox">
                <div class="member_avatar">
                    <img :src="user.avatar_small_url" class="radius-circle " alt=""/>
                </div>
                <ul class="member_name">
                    <li class="nickname"> ${user.nickname}</li>
                    <li> ${user.uid}</li>
                </ul>
            </td>
            <td>${user.charm_value}</td>
            <td> ${user.wealth_value}</td>
            <td> ${user.hi_coins}</td>
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

        $.authGet('/partner/private_unions/users',
            {
                page: vm.current_page,
                start_at_time: "{{ start_at_time }}",
                end_at_time: "{{ end_at_time }}"
            }, function (resp) {
                var users = resp.users;
                vm.datas = [];
                $.each(users, function (index, item) {
                    vm.datas.push(item);
                    vm.total_page = resp.total_page;
                })

            })
    }

    loadData();
</script>
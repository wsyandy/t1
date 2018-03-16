<form method="post" action="/partner/unions/users">
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
    <table class="table table-hover">
        <thead>
        <tr>
            <th>成员信息</th>
            <th>收入</th>
            <th>房主时长</th>
            <th>上麦时长</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="flexbox">
                <div class="member_avatar">
                    <img src="{{ current_user.avatar_small_url }}" class="radius-circle " alt=""/>
                </div>
                <ul class="member_name">
                    <li class="nickname "> {{ current_user.nickname }} <span class="president">会长</span></li>
                    <li> {{ current_user.id }}</li>
                </ul>
            </td>
            <td>{{ current_user.income }}</td>
            <td> {{ current_user.host_broadcaster_time_text }}</td>
            <td> {{ current_user.broadcaster_time_text }}</td>
        </tr>

        <tr v-for="user in datas">
            <td class="flexbox">
                <div class="member_avatar">
                    <img :src="user.avatar_small_url" class="radius-circle " alt=""/>
                </div>
                <ul class="member_name">
                    <li class="nickname"> ${user.nickname}</li>
                    <li> ${user.id}</li>
                </ul>
            </td>
            <td>${user.income}</td>
            <td> ${user.host_broadcaster_time_text}</td>
            <td> ${user.broadcaster_time_text}</td>
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

        $.authGet('/partner/unions/users',
            {
                page: vm.current_page,
                stat_at: "{{ stat_at }}",
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
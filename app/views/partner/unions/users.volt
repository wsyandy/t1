<form method="post" action="">
    <div class="search">

        <div class="form-group ">
            <label class="search_label">用户ID</label>
            <input type="text" class="input search_input" name="userid" placeholder="请输入用户ID"
                   data-validate="required:请输入用户ID"/>
        </div>

        <div class="form-group">
            <label class="search_label">时间</label>
            <input type="text" class="input search_input " id="timestart" name="timestart" placeholder="起始时间"/>
            <label class="search_label">到</label>
            <input type="text" class="input search_input" id="timesend" name="timesend" placeholder="结束时间"/>
        </div>
        <div class="form-group ">
            <div class="field padding-large-left">
                <button class="button bg-dot icon-search " type="submit"> 搜索</button>
            </div>

        </div>

    </div>
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
                        <img src="{{ union.user.avatar_small_url }}" class="radius-circle " alt=""/>
                    </div>
                    <ul class="member_name">
                        <li class="nickname "> {{ union.user.nickname }} <span class="president">会长</span></li>
                        <li> {{ union.user.id }}</li>
                    </ul>
                </td>
                <td>22</td>
                <td> 10:02:32</td>
                <td> 10:02:32</td>
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
                        <a href="">首页</a>
                        <a href="">上一页</a>
                        <span class="current">${current_page}</span>

                        <a href="">2</a>
                        <a href="">3</a>

                        <a href="">下一页</a>
                        <a href="">尾页</a>
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
            current_page: 0,
            total_entries: 0,
            total_page: 1
        },
        methods: {}
    };

    vm = XVue(opts);

    function loadData() {

        vm.current_page++;

        if (vm.current_page > vm.total_page) {
            return;
        }

        $.authGet('/partner/unions/users', {page: vm.current_page}, function (resp) {
            var users = resp.users;

            $.each(users, function (index, item) {
                vm.datas.push(item);
                vm.total_page = resp.total_page;
            })

        })
    }

    loadData();
</script>
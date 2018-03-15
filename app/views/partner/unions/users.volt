
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
                        <img src="images/avatar.jpg" class="radius-circle " alt=""/>
                    </div>
                    <ul class="member_name">
                        <li class="nickname "> 会长始终显示第一位 <span class="president">会长</span></li>
                        <li> 13420925611</li>
                    </ul>
                </td>
                <td>现金</td>
                <td> 10:02:32</td>
                <td> 10:02:32</td>
            </tr>
            <tr>
                <td class="flexbox">
                    <div class="member_avatar">
                        <img src="images/avatar.jpg" class="radius-circle " alt=""/>
                    </div>
                    <ul class="member_name">
                        <li class="nickname"> 成员昵称</li>
                        <li> 13420925611</li>
                    </ul>
                </td>
                <td>现金</td>
                <td> 10:02:32</td>
                <td> 10:02:32</td>
            </tr>

            </tbody>

            <tfoot>
            <tr>
                <td colspan="8">
                    <div class="pagelist"><a href="">上一页</a> <span class="current">1</span><a href="">2</a><a
                                href="">3</a><a href="">下一页</a><a href="">尾页</a></div>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</form>

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
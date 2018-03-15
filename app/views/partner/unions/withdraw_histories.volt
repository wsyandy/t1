{{ block_begin('head') }}
{{ theme_css('/partner/css/jedate-select.css', '/partner/css/jedate.css') }}
{{ theme_js('/partner/css/jquery.jedate.min.js', '/partner/css/jedate-select.js') }}
{{ block_end() }}
<form method="post" action="">
    <div class="search">
        <div class="form-group ">
            <label class="search_label">待结算金额：</label>
            <span class="search_span">
                  343（元）
              </span>

        </div>
        <div class="form-group ">
            <label class="search_label">已结算金额：</label>
            <span class="search_span">
                  343（元）
              </span>

        </div>

    </div>
    <div class="admin-panel padding">
        <table class="table table-hover  ">
            <thead>
            <tr>
                <th>结算金额（元）</th>
                <th>结算状态</th>
                <th>结算日期</th>
            </tr>
            </thead>
            <tbody>
            <tr>

                <td>234</td>
                <td>待结算</td>
                <td>2017-12-21 12:07:32</td>
            </tr>
            <tr>

                <td>234</td>
                <td>待结算</td>
                <td>2017-12-21 12:07:32</td>
            </tr>
            <tr>

                <td>234</td>
                <td>待结算</td>
                <td>2017-12-21 12:07:32</td>
            </tr>
            <tr>

                <td>234</td>
                <td>待结算</td>
                <td>2017-12-21 12:07:32</td>
            </tr>
            <tr>

                <td>234</td>
                <td>待结算</td>
                <td>2017-12-21 12:07:32</td>
            </tr>
            <tr>

                <td>234</td>
                <td>待结算</td>
                <td>2017-12-21 12:07:32</td>
            </tr>
            <tr>

                <td>234</td>
                <td>待结算</td>
                <td>2017-12-21 12:07:32</td>
            </tr>
            <tr>

                <td>234</td>
                <td>待结算</td>
                <td>2017-12-21 12:07:32</td>
            </tr>
            <tr>

                <td>234</td>
                <td>待结算</td>
                <td>2017-12-21 12:07:32</td>
            </tr>
            <tr>

                <td>234</td>
                <td>待结算</td>
                <td>2017-12-21 12:07:32</td>
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

    function del(id) {
        if (confirm("您确定要删除吗?")) {

        }
    }

    $("#checkall").click(function () {
        $("input[name='id[]']").each(function () {
            if (this.checked) {
                this.checked = false;
            }
            else {
                this.checked = true;
            }
        });
    })

    function DelSelect() {
        var Checkbox = false;
        $("input[name='id[]']").each(function () {
            if (this.checked == true) {
                Checkbox = true;
            }
        });
        if (Checkbox) {
            var t = confirm("您确认要删除选中的内容吗？");
            if (t == false) return false;
        }
        else {
            alert("请选择您要删除的内容!");
            return false;
        }
    }

</script>
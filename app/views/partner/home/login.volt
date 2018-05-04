<div style="width: 250px; margin: 100px auto;">
    <form action="/partner/home/login" id="login_form" class="ajax_form" method="post">
        <div class="form-group">
            <input name="union_id" type="text" class="form-control" placeholder="用户"/>
        </div>
        <div class="form-group">
            <input type="password" name="password" class="form-control" placeholder="密码"/>
        </div>
        <input type="submit" class="btn btn-primary" value="登录"/>
    </form>
</div>
<script>
    var opts = {
        data: {
            agreement: true,
            upload_status: false
        },
        methods: {}
    };

    vm = XVue(opts);
</script>
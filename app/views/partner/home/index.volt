<div class="bg"></div>
<div class="container">
    <div class="line bouncein">
        <div class="xs6 xm4 xs3-move xm4-move">
            <div style="height:150px;"></div>
            <div class="media media-y margin-big-bottom">
            </div>
            <form action="index.html" method="post">
                <div class="panel loginbox">
                    <div class="login_title"><h1>Hi语音公会-登录</h1></div>
                    <div class="panel-body">
                        <div class="form-group ">
                            <div class="field ">
                                <span class="login_tel"></span>
                                <input type="text" class="input login_input" name="mobile" placeholder="请输入你的注册手机"
                                       data-validate="required:请输入你的注册手机"/>

                            </div>
                        </div>
                        <div class="form-group">
                            <div class="field">
                                <span class="login_pwd"></span>
                                <input type="password" class="input login_input" name="password1"
                                       placeholder="请输入6～16位密码" data-validate="required:请输入6～16位密码"/>
                            </div>
                        </div>

                    </div>
                    <div class="login_btn">
                        <input type="submit" value="登录">
                    </div>
                    <div class="login_register">
                        <a href="/partner/unions/register">免费注册</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
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
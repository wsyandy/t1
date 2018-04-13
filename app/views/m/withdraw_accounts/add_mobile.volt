{{ block_begin('head') }}
{{ theme_css('/m/withdraw_histories/css/apple', '/m/withdraw_accounts/css/add_mobile') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <div class="ver_tips">为了您的账号安全，请先完成短信验证绑定</div>
    <ul class="ver_box">
        <li class="ver_list">
            <input class="ver_tel" type="number" v-model="ver_tel" type="text" placeholder="请先输入手机号码" @input="verTel"
                   oninput="if(value.length>11)value=value.slice(0,11)">
        </li>
        <li class="ver_list">
            <input class="ver_code" v-model="ver_code" type="text" placeholder="请输入验证码" :readonly="readonly"
                   @input="verCode">
            <div :class="['get_code',{'cur':isFull}]" @click="getCode">
                <span v-text="auth_text"></span>
            </div>
        </li>
    </ul>

    <a :class="['btn_disabled',{'btn_submit': !disabled}]" @click.stop="createWithdrawAccount"> 提交 </a>
</div>

<script>
    var time = 60;
    {% if isDevelopmentEnv() %}
    time = 3;
    {% endif %}

    var opts = {
        data: {
            ico_clear: "images/ico_clear.png",
            isFull: false,
            readonly: true,
            disabled: true,
            ver_tel: '',
            ver_code: '',
            auth_time: 0,
            auth_text: '获取验证码',
            sms_token: ''
        },
        created: function () {
        },
        methods: {
            verTel: function () {
                this.isFull = true;

            },
            getCode: function () {
                if (!this.isFull || this.auth_time != 0) {
                    return false;
                }

                this.auth_time = time;
                this.readonly = false;

                var data = {
                    sid: '{{ sid }}',
                    code: '{{ code }}',
                    mobile: this.ver_tel
                };

                $.authPost('/m/withdraw_accounts/send_auth', data, function (resp) {
                    if (resp.error_code != 0) {
                        alert(resp.error_reason);
                        vm.auth_time = 0;
                    } else {
                        vm.sms_token = resp.sms_token;
                        vm.timeChange();
                    }
                })
            },
            verCode: function () {
                this.disabled = false
            },
            timeChange: function () {
                timer = setInterval(function () {
                            this.auth_time--;
                            this.auth_text = '重发(' + (this.auth_time >= 10 ? this.auth_time : '0' + this.auth_time) + 's)';
                            if (this.auth_time == 0) {
                                if (timer != null) {
                                    clearInterval(timer);
                                    this.auth_text = "重新获取";
                                }
                            }
                            console.log(this.auth_time);
                        }.bind(vm), 1000
                );
            },
            createWithdrawAccount: function () {
                var data = {
                    sid: '{{ sid }}',
                    code: '{{ code }}',
                    auth_code: this.ver_code,
                    sms_token: this.sms_token,
                    mobile: this.ver_tel
                };

                console.log(data);

                $.authPost('/m/withdraw_accounts/create', data, function (resp) {
                    if (resp.error_code != 0) {
                        alert(resp.error_reason);
                    }
                    if (resp.error_url) {
                        location.href = resp.error_url;
                    }
                });
            }
        }
    };

    vm = XVue(opts);

    $(function () {
        pushHistory();
    });

    //解决ios后退无法刷新
    function pushHistory() {
        window.addEventListener("popstate", function (e) {
            self.location.reload();
        }, false);
        var state = {
            title: "",
            url: "#"
        };
        window.history.replaceState(state, "", "#");
    }
</script>
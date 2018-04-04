{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/apply_exit') }}
{{ block_end() }}

<div class="application_details_box" id="app" v-cloak>
    <div class="applicat_wrap">
        <div class="application_wrap_img">
            <img :src="avatar_small_url">
        </div>
        <h3>${user.nickname}
            <span :class="[user.sex?'men':'women']">${user.age}</span>
        </h3>
        <div class="love_wealth">
            <span>魅力值：${user.charm_value}</span>
            <span>财富值：${user.wealth_value}</span>
        </div>
        <h4>申请退出家族</h4>
        <div class="application_btn" @click="applyExitAction">
            <span class="agree">同意</span>
        </div>
    </div>
</div>


<script>
    var opts = {
        data: {
            avatar_small_url:'{{ user.avatar_small_url }}',
            user:{{ user }},
            sid:"{{ sid }}",
            code:"{{ code }}",
            user_id:"{{ user_id }}",
            click_status:false
        },
        methods: {
            applyExitAction: function () {
                if (this.click_status) {
                    return;
                }

                this.click_status = true;
                var data = {
                    sid: this.sid,
                    code: this.code,
                    user_id:this.user_id
                };

                $.authPost("/m/unions/confirm_apply_exit", data, function (resp) {
                    vm.click_status = false;
                    if (resp.error_code != 0) {
                        alert(resp.error_reason);
                        return;
                    }

                    $('.agree').html('已同意');
                    var url = "/m/unions/index&sid=" + vm.sid + "&code=" + vm.code;
                    location.href = url;
                    return;
                });
            }
        }
    };
    vm = XVue(opts);


</script>
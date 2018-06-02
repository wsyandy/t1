{{ block_begin('head') }}
{{ theme_css('/m/css/award_index.css') }}
{{ theme_js('/m/js/resize.js') }}
{{ block_end() }}
<div id="app" class="grab">
    <!-- 弹框提醒 -->
    <div class="remind_box">
        <div class="remind_wrap">
            <div class="close_box">
                <i></i>
            </div>
            <p v-text="error_reason"></p>
        </div>
    </div>
    <div class="rank_box">
        <div class="logo">
            <i></i>
            <span>Hi语音</span>
        </div>
        <div class="text_bg">
            <h2>{{ user.nickname }}，恭喜您 <img src="/m/images/hi_line.png" class="hi_line"></h2>
            <p>获得${award_history.created_at_text}扶持奖励</p>
            <p>${ award_history.amount }${ award_history.type_text }</p>
            <div class="hi_btn" @click="getAward()">${award_history.status_text}</div>
            <img src="/m/images/line.png" class="line">
            <div class="text_pic">
                <img src="/m/images/person.png">
                <span>感谢您对我们平台的支持，希望您在{{ product_channel_name }}的世界里玩的快乐！</span>
            </div>
        </div>
        <div class="hi_bottom">
            <h3>最终解释权归{{ product_channel_name }}官方所有</h3>
            <p class="line"></p>
        </div>
    </div>
</div>
<script type="text/javascript">
    var opts = {
        data: {
            sid: "{{ sid }}",
            code: "{{ code }}",
            award_history:{{ award_history_json }},
            error_reason: ''
        },
        methods: {
            getAward: function () {
                if (!vm.award_history.status) {
                    vm.error_reason = '您已经领取过了哦，快去您的账户中请查收！';
                    $('.remind_box').show();
                    return;
                }
                var data = {
                    sid: vm.sid,
                    code: vm.code,
                    award_history_id: vm.award_history.id
                };
                $.authPost('/m/award_histories/get_awards', data, function (resp) {
                    vm.error_reason = resp.error_reason;
                    $('.remind_box').show();
                    if (!resp.error_code) {
                        $('.hi_btn').addClass('get_btn').html('已领取');
                    }
                })
            }
        }
    };

    vm = XVue(opts);

    $(function () {
        if (!vm.award_history.status) {
            $('.hi_btn').addClass('get_btn');
        }
        // 弹框关闭
        $('.remind_box').hide();
        $('.close_box').click(function () {
            $('.remind_box').hide();
        })
    })
</script>
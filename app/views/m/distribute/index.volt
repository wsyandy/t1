{{ block_begin('head') }}
{{ theme_css('/m/css/distribute_apple.css','/m/css/distribute_index.css') }}
{{ theme_js('/m/js/resize.js') }}
{{ block_end() }}
<div id="app">
    <div class="invite_header">
        <div class="invite_info">
            <div class="invite_bonus" @click="toBonus">
                <p>奖励</p>
                <span>${total_amount}</span>
            </div>
            <div class="invite_line"></div>
            <div class="invite_num">
                <p>已邀请人数</p>
                <span>${user_num}</span>
            </div>
        </div>
        <div class="invite_btn" @click="toPage">
            <img class="invite_star" src="/m/images/invite_star.png" alt="">
            <span>我的推广页</span>
        </div>
    </div>

    <div class="invite_rules">
        <div class="rules_title">
            <span>活动规则</span>
        </div>
        <ul class="rules_list">
            <li>
                1. A通过你的推广页注册账号后，你将会获得<span>20</span>钻石价值<span>2</span>元的奖励
            </li>
            <li>2. A通过你的推广页注册账号后，你将会获得A充值钻石的<span>5%</span>的奖励</li>
            <li>3. A通过你的推广页注册账号后，你将会获得A拉取的用户充值钻石的<span>1%</span>的奖励</li>

        </ul>
    </div>
</div>
<script>
    var opts = {
        data: {
            total_amount: '{{ total_amount }}',
            user_num: "{{ user_num }}",
            sid: "{{ sid }}",
            code: "{{ code }}"
        },

        methods: {
            toPage: function () {
                var url = "/m/distribute/page";
                vm.redirectAction(url + '?sid=' + vm.sid + '&code=' + vm.code);
            },
            toBonus:function () {
                var url = "/m/distribute/detail";
                vm.redirectAction(url + '?sid=' + vm.sid + '&code=' + vm.code);
            }
        }
    };
    vm = XVue(opts);

</script>

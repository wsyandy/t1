{{ block_begin('head') }}
{{ theme_css('/m/css/distribute_apple.css','/m/css/distribute_detail.css','/m/css/distribute_swiper.min.css') }}
{{ theme_js('/m/js/swiperTab.js','/m/js/swiper.jquery.min.js') }}
{{ block_end() }}
<div id="app">
    <ul class="swiperTab">
        <li>
            <span>已邀请的</span>
            <span class="border"></span>
        </li>
        <li>
            <span>充值分成</span>
            <span class="border"></span>
        </li>
    </ul>
    <div class="swiper-container">
        <div class="swiper-wrapper">
            <!--已邀请的-->
            <div class="swiper-slide">
                <ul class="invite_list" v-for="register_account_history in register_account_histories">
                    <li>
                        <img class="user_avatar" :src="register_account_history.user_avatar_url" alt="">
                        <div class="invite_info">
                            <div class="user_info">
                                <p class="user_name">${register_account_history.user_nickname}</p>
                                <span class="invite_time">${register_account_history.created_at}</span>
                            </div>
                            <div class="invite_reward">
                                <span>奖励＋${register_account_history.amount}</span>
                                <img class="reward_diamond" src="/m/images/reward_diamond.png" alt="">
                            </div>
                        </div>
                    </li>
                </ul>

            </div>
            <!--充值分成-->
            <div class="swiper-slide">
                <ul class="invite_list" v-for="pay_account_history in pay_account_histories">
                    <li>
                        <img class="user_avatar" :src="pay_account_history.user_avatar_url" alt="">
                        <div class="invite_info">
                            <div class="user_info">
                                <p class="user_name">${pay_account_history.user_nickname}</p>
                                <span class="invite_time">${pay_account_history.created_at}</span>
                            </div>
                            <div class="invite_reward">
                                <span>奖励＋${pay_account_history.amount}</span>
                                <img class="reward_diamond" src="/m/images/reward_diamond.png" alt="">
                            </div>
                        </div>
                    </li>
                </ul>

            </div>
        </div>
    </div>
</div>

<script>
    /*swiper选项卡切换*/
    $(function () {
        $('.swiperTab > li').eq(0).addClass('active');
        tabs('.swiperTab > li', '.swiper-container', 'active');
        distributeRegisterForBonus();
        distributePayForBonus();
    });
    var opts = {
        data: {
            register_account_histories: [],
            pay_account_histories:[]
        },

        methods: {

        }
    };


    function distributeRegisterForBonus() {
        var data = {
            sid: "{{ sid }}",
            code: "{{ code }}"
        };

        $.authGet('/m/distribute/distribute_register_bonus', data, function (resp) {
            vm.register_account_histories = resp.account_histories;
        })
    }
    function distributePayForBonus() {
        var data = {
            sid: "{{ sid }}",
            code: "{{ code }}"
        };
        $.authGet('/m/distribute/distribute_pay_bonus', data, function (resp) {
            vm.pay_account_histories = resp.account_histories;
        })
    }

    vm = XVue(opts);
</script>

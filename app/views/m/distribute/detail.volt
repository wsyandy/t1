{{ block_begin('head') }}
{{ theme_css('/m/css/distribute_apple.css','/m/css/distribute_detail.css','/m/css/distribute_swiper.min.css') }}
{{ theme_js('/m/js/swiperTab.js','/m/js/swiper.jquery.min.js') }}
{{ block_end() }}
<div id="app">
    <ul class="swiperTab">
        <li @click="cut('register')">
            <span>已邀请的</span>
            <span class="border"></span>
        </li>
        <li @click="cut('pay')">
            <span>充值分成</span>
            <span class="border"></span>
        </li>
    </ul>
    <div class="swiper-container">
        <div class="swiper-wrapper">
            <!--已邀请的-->
            <div class="swiper-slide">
                <ul class="invite_list" v-for="account_history in account_histories">
                    <li>
                        <img class="user_avatar" :src="account_history.user_avatar_url" alt="">
                        <div class="invite_info">
                            <div class="user_info">
                                <p class="user_name">${account_history.user_nickname}</p>
                                <span class="invite_time">${account_history.created_at}</span>
                            </div>
                            <div class="invite_reward">
                                <span>奖励＋${account_history.amount}</span>
                                <img class="reward_diamond" src="/m/images/reward_diamond.png" alt="">
                            </div>
                        </div>
                    </li>
                </ul>

            </div>
            <!--充值分成-->
            <div class="swiper-slide">
                <ul class="invite_list" v-for="account_history in account_histories">
                    <li>
                        <img class="user_avatar" :src="account_history.user_avatar_url" alt="">
                        <div class="invite_info">
                            <div class="user_info">
                                <p class="user_name">${account_history.user_nickname}</p>
                                <span class="invite_time">${account_history.created_at}</span>
                            </div>
                            <div class="invite_reward">
                                <span>奖励＋${account_history.amount}</span>
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
        distributeForBonus('register');
    });
    var opts = {
        data: {
            account_histories: []
        },

        methods: {
            cut: function (type) {
                distributeForBonus(type);
            }
        }
    };


    function distributeForBonus(type) {
        var data = {
            sid: "{{ sid }}",
            code: "{{ code }}",
            type: type
        };
        $.authGet('/m/distribute/distribute_bonus', data, function (resp) {
            vm.account_histories = resp.account_histories;
        })
    }


    vm = XVue(opts);
</script>

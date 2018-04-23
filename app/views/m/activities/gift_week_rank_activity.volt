{{ block_begin('head') }}
{{ theme_css('/m/css/gift_week_rank_activity') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak="">
    <div class="week_activity">
        <span class="week_title_bg"></span>
        <div class="week_title_textbg">
            <span>戴上金链，穿上紧身裤</span>
            <span>将头发梳成社会人的模样~</span>
            <span>然后在手臂纹上小猪佩奇</span>
            <span>将叛逆和乖巧完美统一</span>
        </div>
        <div class="week_title_textbox">
            <span>现如今，想要混社会</span>
            <span>你要没一个小猪佩佩奇</span>
            <span>都不敢起床上班~</span>
        </div>
        <img class="week_process" src="/m/images/week_process.png" alt="流程"/>
        <div class="week_title_textbox">
            <span>如果你都会了</span>
            <span>恭喜你</span>
            <span>成功和我一样</span>
            <span>成了社会人</span>
            <span class="week_get_text"></span>
        </div>
        <span class="week_title_type charm_reward"></span>
        <ul class="week_charm_rewardul">
            <li>
                <span class="type">本周总榜</span>
                <span class="center">第一名奖励</span>
                <span class="behind">1000钻礼物冠名权</span>
            </li>
            <li>
                <span class="type">社会猫</span>
                <span class="center">第一名奖励</span>
                <span class="behind">500钻礼物冠名权</span>
            </li>
            <li>
                <span class="type">小猪佩奇</span>
                <span class="center">第一名奖励</span>
                <span class="behind">100钻礼物冠名权</span>
            </li>
            <li>
                <span class="type">肥皂</span>
                <span class="center">第一名奖励</span>
                <span class="behind">10钻礼物冠名权</span>
            </li>
        </ul>
        <span class="week_rules_tltle"></span>
        <div class="week_rules_box">
            <p><span>1.</span><span>所有冠名权的时限均只有一周</span></p>
            <p><span>2.</span><span>用户在活动期间收到礼物，每收到1个钻石礼物，用户的魅力值+1，每送出一个1个钻石礼物，贡献值+1;</span></p>
            <p><span>3.</span><span>礼物榜按用户收到对应礼物（社会猫，小猪佩奇，肥皂）的个数进行排名</span></p>
            <p><span>4.</span><span>活动时间为2018年4月23日18时——2018年4月29日24时</span></p>
            <p><span>5.</span><span>新礼物冠名请于每周一上午14:00提交官方QQ：3407150190逾时按获奖ID作为冠名内容；</span></p>
            <p><span>6.</span><span>活动结果将会在每周一12:00公布，请保持关注</span></p>
        </div>
        <span class="week_title_type real_time"></span>
        <div class="week_countdown">
            <span id="time_text">  活动未开始 </span>
        </div>
        <ul class="week_list_tab">
            <li @click="selectTab(0)" :class="[tab_index==0&&'cur']"><img src="/m/images/week_total_list.png"
                                                                          alt="icon"><span>本周总榜</span></li>
            <li @click="selectTab(1)" :class="[tab_index==1&&'cur']"><img src="/m/images/week_cat_icon.png"
                                                                          alt="icon"><span>社会猫</span></li>
            <li @click="selectTab(2)" :class="[tab_index==2&&'cur']"><img src="/m/images/week_pig_icon.png"
                                                                          alt="icon"><span>小猪佩奇</span></li>
            <li @click="selectTab(3)" :class="[tab_index==3&&'cur']"><img src="/m/images/week_soap_icon.png"
                                                                          alt="icon"><span>肥皂</span></li>
        </ul>
        <div class="week_top_three" v-if="users.length">
            <div class="week_top_three_li" v-if="users.length > 1">
                <div class="header">
                    <span class="two"></span>
                    <img :src="users[1].avatar_small_url" alt="">
                </div>
                <p class="two">${users[1].nickname}</p>
                <span>魅力值：${users[1].charm_value}</span>
            </div>
            <div class="week_top_three_li">
                <div class="neo">
                    <span></span>
                    <img :src="users[0].avatar_small_url" alt=""></div>
                <p>${users[0].nickname}</p>
                <span>魅力值：${users[0].charm_value}</span>
            </div>
            <div class="week_top_three_li" v-if="users.length > 2">
                <div class="header">
                    <span class="three"></span>
                    <img :src="users[2].avatar_small_url" alt="">
                </div>
                <p class="three">${users[2].nickname}</p>
                <span>魅力值：${users[2].charm_value}</span>
            </div>
        </div>
        <ul class="week_list_content" v-if="users.length > 3">
            <li v-for="user in users.slice(3)">
                <span class="level">${user.rank}</span>
                <img :src="user.avatar_small_url" alt="头像"/>
                <span class="name">${user.nickname}</span>
                <span>魅力值：${user.charm_value}</span>
            </li>
        </ul>

        <p class="week_hint_text">活动最终解释权归Hi语音官方团队</p>
    </div>
</div>

<script>
    var opts = {
        data: {
            tab_index: 1,
            users: []
        },
        created: function () {
            this.getUsers();
        },
        methods: {
            getUsers: function () {
                var data = {
                    sid: '{{ sid }}',
                    code: '{{ code }}',
                    index: this.tab_index
                };
                $.authGet('/m/activities/gift_week_rank_activity', data, function (resp) {
                    if (resp.error_code == 0) {
                        $.each(resp.users, function (index, item) {
                            vm.users.push(item);
                        });
                        console.log(vm.users);
                    }
                });
            },
            selectTab: function (index) {
                this.tab_index = index;
                this.users = [];
                this.getUsers();
            }
        }
    };

    vm = XVue(opts);


    $(function () {

        var end_time = "{{ end_time }}";

        var start_time = "{{ start_time }}";

        countdown(end_time, start_time)
    });


    function countdown(end_time, start_time) {
        var EndTime = new Date(end_time) || [];
        var StartTime = new Date(start_time) || [];

        var NowTime = new Date().getTime();

        var text = '';

        if (NowTime < StartTime) {
            EndTime = StartTime;
            text = "距离活动开始：";
        } else {
            text = "剩余时间：";
        }

        var total_micro_second = EndTime - NowTime || [];

        if (total_micro_second > 0) {
            setTimeout(function () {
                total_micro_second -= 1000;
                countdown(end_time, start_time);
            }, 1000)
        } else {
            total_micro_second = 0;
        }

        var time = '活动已结束';

        if (total_micro_second > 0) {
            // 总秒数
            var second = Math.floor(total_micro_second / 1000)
            // 天数
            var day = Math.floor(second / 3600 / 24)
            // 小时
            var hr = Math.floor(second / 3600 % 24)
            // 分钟
            var min = Math.floor(second / 60 % 60)
            // 秒
            var sec = Math.floor(second % 60)
            // 时间格式化输出，如11:03 25:19 每1s都会调用一次
            second = toTwo(second)
            day = toTwo(day)
            hr = toTwo(hr)
            min = toTwo(min)
            sec = toTwo(sec)

            // 渲染倒计时时钟
            time = text + day + "天" + hr + ":" + min + ":" + sec;
        }

        var _time = document.getElementById("time_text");

        _time.innerText = time;

    }
    /**
     * 封装函数使1位数变2位数
     */
    function toTwo(n) {
        n = n < 10 ? "0" + n : n
        return n
    }

</script>

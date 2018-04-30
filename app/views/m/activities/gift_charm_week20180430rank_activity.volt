{{ block_begin('head') }}
{{ theme_css('/m/css/gift_week_20180430_rank_activity_2.css') }}
{{ block_end() }}

<script>
    (function (doc, win) {
        var docEl = doc.documentElement,
            resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
            recalc = function () {
                var clientWidth = docEl.clientWidth;
                if (!clientWidth) return;
                docEl.style.fontSize = 100 * (clientWidth / 750) + 'px';
            };

        if (!doc.addEventListener) return;
        win.addEventListener(resizeEvt, recalc, false);
        doc.addEventListener('DOMContentLoaded', recalc, false);
    })(document, window);
</script>

<div id="app" class="week_activity">
    <div class="week_activity_banner"></div>
    <span class="week_title_type week_charm_top1"></span>
    <div class="week_rules_box">
        <div class="week_rules_box_border">
            <ul class="week_charm_topul">
                {% if last_week_charm_rank_list_user %}
                    <li>
                        <span class="type">总榜</span>
                        <img src="/m/images/week_total_list.png" alt="头像"/>
                        <span class="name">{{ last_week_charm_rank_list_user['nickname'] }}</span>
                        <span>魅力值：{{ last_week_charm_rank_list_user['charm_value'] }}</span>
                    </li>
                {% endif %}
                {% for index, last_gift in last_gifts %}
                    <li>
                        <span class="type">{{ last_gift.name }}</span>
                        <img src="{{ last_gift.image_small_url }}" alt="头像"/>
                        {% if last_activity_rank_list_users %}
                            <span class="name">{{ last_activity_rank_list_users[index]['nickname'] }}</span>
                            <span>魅力值：{{ last_activity_rank_list_users[index]['charm_value'] }}</span>
                        {% endif %}
                    </li>
                {% endfor %}
            </ul>
        </div>
        <span class="week_rules_waves_icon"></span>
        <span class="week_rules_chair_icon"></span>
    </div>
    <div class="week_rules_slogan"></div>
    <span class="week_title_type week_charm_gift"></span>
    <div class="week_rules_box">
        <div class="week_rules_box_border">
            <ul class="week_rules_giftul">
                {% for gift in gifts %}
                    <li>
                        <img src="{{ gift.image_small_url }}" alt="">
                        <span>{{ gift.name }}</span>
                    </li>
                {% endfor %}
            </ul>
        </div>
        <span class="week_rules_waves_iconr"></span>
        <span class="week_rules_wire_icon"></span>
    </div>
    <span class="week_title_type charm_reward"></span>
    <ul class="week_charm_rewardul">
        <li>
            <span class="type">本周总榜</span>
            <span class="center">第一名奖励</span>
            <span class="behind">1000钻礼物冠名权</span>
        </li>
        <li>
            <span class="type">法拉利</span>
            <span class="center">第一名奖励</span>
            <span class="behind">500钻礼物冠名权</span>
        </li>
        <li>
            <span class="type">寿司点心</span>
            <span class="center">第一名奖励</span>
            <span class="behind">100钻礼物冠名权</span>
        </li>
        <li>
            <span class="type">金条</span>
            <span class="center">第一名奖励</span>
            <span class="behind">10钻礼物冠名权</span>
        </li>
    </ul>
    <span class="week_title_type week_rules_tltle"></span>
    <div class="week_rules_box">
        <div class="week_rules_box_border">
            <p><span>1.</span><span>所有冠名权的时限均只有一周</span></p>
            <p><span>2.</span><span>用户在活动期间收到礼物，每收到1个钻石礼物，用户的魅力值+1</span></p>
            <p><span>3.</span><span>礼物榜按用户收到对应礼物（法拉利，寿司点心，金条）的个数进行排名</span></p>
            <p><span>4.</span><span>活动时间为{{ activity.start_at_text }}——{{ activity.end_at_text }}</span></p>
            <p><span>5.</span><span>新礼物冠名请于每周一上午14:00提交官方QQ：3407150190逾时按获奖ID作为冠名内容；</span></p>
        </div>
        <span class="week_rules_waves_iconr"></span>
        <span class="week_rules_wire_icon"></span>
    </div>
    <span class="week_title_type real_time"></span>
    <div class="week_countdown">
        <span id="time_text">  活动未开始 </span>
    </div>
    <ul class="week_list_tab">
        <li @click="selectTab(1,0)" :class="[tab_index==1&&'cur']"><img src="/m/images/week_total_list.png" alt="icon">
            <span>总榜</span>
        </li>
        {% for index, gift in gifts %}
            {% if index < 3 %}
                <li @click.stop="selectTab({{ index + 2 }}, {{ gift.id }})" :class="[tab_index=={{ index + 2 }}&&'cur']"><img
                            src="{{ gift.image_small_url }}"
                            alt="icon"><span>{{ gift.name }}</span>
                </li>
            {% endif %}
        {% endfor %}
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

<script>

    var opts = {
        data: {
            tab_index: 2,
            users: []
        },
        created: function () {
            this.getUsers('{{ gifts[0].id }}');
        },
        methods: {
            getUsers: function (gift_id) {
                var data = {
                    sid: '{{ sid }}',
                    code: '{{ code }}',
                    gift_id: gift_id,
                    id: '{{ id }}'
                };
                $.authGet('/m/activities/gift_charm_week20180430rank_activity', data, function (resp) {
                    vm.users = [];

                    if (resp.error_code == 0) {
                        $.each(resp.users, function (index, item) {
                            vm.users.push(item);
                        });
                    }
                });
            },
            selectTab: function (index, gift_id) {
                this.tab_index = index;
                this.getUsers(gift_id);
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
            time = text + parseInt(day) + "天" + parseInt(hr) + ":" + parseInt(min) + ":" + parseInt(sec);
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
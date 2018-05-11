{{ block_begin('head') }}
{{ theme_css('/m/css/gift_week_20180514_rank_activity.css') }}
{{ block_end() }}

<script>
    (function(doc, win) {
        var docEl = doc.documentElement,
            resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
            recalc = function() {
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
    <div id="week_activity" class="week_activity">
        <span class="week_title_type one_title_type"><img src="/m/images/title_meili_20180514.png"></span>
        <div class="week_rules_box week_rules_background">
            <ul class="week_charm_topul">


                {% if last_week_charm_rank_list_user %}
                    <li>
                        <span class="type">总榜</span>
                        <img src="/m/images/m_zong_20180514.png" alt="头像"/>
                        <span class="name">{{ last_week_charm_rank_list_user['nickname'] }}</span>
                        <span>魅力值：{{ last_week_charm_rank_list_user['charm_value'] }}</span>
                    </li>
                {% endif %}
                {% for index, last_gift in last_gifts %}
                    {% if index < 3 %}
                        <li>
                            <span class="type">{{ last_gift.name }}</span>
                            <img src="{{ last_gift.image_small_url }}" alt="头像"/>
                            {% if last_activity_rank_list_users %}
                                <span class="name">{{ last_activity_rank_list_users[index]['nickname'] }}</span>
                                <span>魅力值：{{ last_activity_rank_list_users[index]['charm_value'] }}</span>
                            {% endif %}
                        </li>
                    {% endif %}
                {% endfor %}

                {#<li>
                    <span class="type">总榜</span>
                    <img src="./images/m_zong.png" alt="头像" />
                    <span class="name">tm.卡卡帝王套齐</span>
                    <span>魅力值：6.6万</span>
                </li>
                <li>
                    <span class="type">社会猫</span>
                    <img src="images/m_bao.png" alt="头像" />
                    <span class="name">tm.卡卡帝王套齐了</span>
                    <span>魅力值：6.6万</span>
                </li>
                <li>
                    <span class="type">小猪佩奇</span>
                    <img src="images/m_dior.png" alt="头像" />
                    <span class="name">tm.卡卡帝王套齐</span>
                    <span>魅力值：6.6万</span>
                </li>
                <li>
                    <span class="type">肥皂</span>
                    <img src="images/m_tea.png" alt="头像" />
                    <span class="name">tm.卡卡帝王套齐了</span>
                    <span>魅力值：6.6万</span>
                </li>#}

            </ul>
        </div>
        <div class="week_rules_slogan">
            <span>他们说每年的情人节很多</span>
            <span>但玫瑰代表爱情</span>
            <span>一年仅一次</span>
            <span>你愿意来找我吗？</span>
        </div>
        <span class="week_title_type"><img src="/m/images/title_xin_20180514.png"></span>
        <div class="week_rules_box new_rules_background">
            <ul class="week_rules_giftul">

                {% for gift in gifts %}
                    <li>
                        <img src="{{ gift.image_small_url }}" alt="">
                        <span>{{ gift.name }}</span>
                    </li>
                {% endfor %}


                {#<li>
                    <img src="./images/gift_meigui.png" alt="">
                </li>
                <li>
                    <img src="./images/gift_love.png" alt="">
                </li>
                <li>
                    <img src="./images/gift_m.png" alt="">
                </li>
                <li>
                    <img src="./images/gift_merry.png" alt="">
                </li>#}

            </ul>
        </div>
        <span class="week_title_type title_z"><img src="/m/images/title_z_20180514.png"></span>
        <ul class="week_charm_rewardul week_rules_box">
            <li>
                <span class="type">本周总榜</span>
                <span class="center">第一名奖励</span>
                <span class="behind">666钻礼物冠名权</span>
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
        <span class="week_title_type title_z"><img src="/m/images/title_guize_20180514.png"></span>
        <div class="week_rules_box week_rules_bg">
            <p style="margin-top:0.3rem;"><span>1、</span><span>所有冠名权的时限均为一周</span></p>
            <p><span>2、</span><span>用户在活动期间收到礼物，每收到1个钻石，用户的魅力值+1</span></p>
            <p><span>3、</span><span>礼物榜按用户收到对应礼物（水晶项链，YSL口红，金粉玫瑰）的个数进行排名</span></p>
            <p><span>4、</span><span>活动时间为2018-05-14 17:00至2018-05-21 00:00</span></p>
            <p><span>5、</span><span>新礼物冠名请于每周一上午14:00提交官方QQ：3407150190逾时按获奖ID昵称作为冠名内容 </span></p>
            <p><span>6、</span><span>活动结果将于每周一12:00公布，请保持关注</span></p>
        </div>
        <span class="week_title_type title_z"><img src="/m/images/title_s_20180514.png"></span>
        <div class="week_countdown">
            <span id="time_text">  剩余时间：2天 </span>
            {#<p>02:22:18</p>#}
        </div>

        {% if time() >= activity.start_at %}
            <ul class="week_list_tab">
                <li @click="selectTab(1,0)" :class="[tab_index==1&&'cur']"><img src="/m/images/week_total_list.png"
                                                                                alt="icon">
                    <span>总榜</span>
                </li>
                {% for index, gift in gifts %}
                    {% if index < 3 %}
                        <li @click.stop="selectTab({{ index + 2 }}, {{ gift.id }})"
                            :class="[tab_index=={{ index + 2 }}&&'cur']"><img
                                    src="{{ gift.image_small_url }}"
                                    alt="icon"><span>{{ gift.name }}</span>
                        </li>
                    {% endif %}
                {% endfor %}
            </ul>
            <ul class="week_list_content" v-if="users.length">
                <li v-for="user,index in users">
                    <span :class="index==0?'neo':(index==1?'two':(index==2?'three':'level'))">${user.rank>3?user.rank:''}</span>
                    <img :src="user.avatar_small_url" alt="头像"/>
                    <span class="name">${user.nickname}</span>
                    <span>魅力值：${user.charm_value}</span>
                </li>
            </ul>
        {% endif %}

        {#<ul class="week_list_tab">
            <li>
                <img src="./images/week_zong.png" alt="icon">
                <span>总榜</span>
            </li>
            <li>
                <img src="./images/week_love.png" alt="icon">
                <span>水晶项链</span>
            </li>
            <li>
                <img src="./images/week_marry.png" alt="icon">
                <span>YSL口红</span>
            </li>
            <li>
                <img src="./images/week_meigui.png" alt="icon">
                <span>金粉玫瑰</span>
            </li>
        </ul>
        <ul class="week_list_content">
            <li>
                <span class="neo"></span>
                <img src="images/s_bg.png" alt="头像" />
                <span class="name">追光者家族</span>
                <span>魅力值：2.2万</span>
            </li>
            <li>
                <span class="two"></span>
                <img src="" alt="头像" />
                <span class="name">追光者家族追光者家族追光者家族</span>
                <span>魅力值：2.2万</span>
            </li>
            <li>
                <span class="three"></span>
                <img src="" alt="头像" />
                <span class="name">追光者家族</span>
                <span>魅力值：2.2万</span>
            </li>
            <li>
                <span class="level">4</span>
                <img src="" alt="头像" />
                <span class="name">追光者家族</span>
                <span>魅力值：2.2万</span>
            </li>
            <li>
                <span class="level">5</span>
                <img src="" alt="头像" />
                <span class="name">追光者家族</span>
                <span>魅力值：2.2万</span>
            </li>
            <li>
                <span class="level">6</span>
                <img src="" alt="头像" />
                <span class="name">追光者家族</span>
                <span>魅力值：2.2万</span>
            </li>
            <li>
                <span class="level">7</span>
                <img src="" alt="头像" />
                <span class="name">追光者家族</span>
                <span>魅力值：2.2万</span>
            </li>
            <li>
                <span class="level">8</span>
                <img src="" alt="头像" />
                <span class="name">追光者家族</span>
                <span>魅力值：2.2万</span>
            </li>
            <li>
                <span class="level">9</span>
                <img src="" alt="头像" />
                <span class="name">追光者家族</span>
                <span>魅力值：2.2万</span>
            </li>
            <li>
                <span class="level">10</span>
                <img src="" alt="头像" />
                <span class="name">追光者家族</span>
                <span>魅力值：2.2万</span>
            </li>
        </ul>#}

        <p class="week_hint_text">活动最终解释权归Hi语音官方团队</p>
    </div>

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
                $.authGet('/m/activities/get_current_activity_rank_list', data, function (resp) {
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
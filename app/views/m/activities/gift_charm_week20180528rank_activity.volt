{{ block_begin('head') }}
{{ theme_css('/m/css/gift_week_20180528_rank_activity2.css') }}
{{ theme_js('/m/js/resize.js') }}
{{ block_end() }}
<div class="vueBox" id="app">
    <img :src="bg_header" class="bg_header" alt="">
    {% if time() >= activity.start_at %}
        <img :src="bg_main" class="bg_main" alt="">
        <img :src="bg_footer" class="bg_footer" alt="">
    {% endif %}
    <img :src="rule_btn" class="rule_btn" alt="" @click="ruleShow">
    <div class="weekly_header">
        <img :src="title01" class="title01" alt="">
        <div class="bg01">
            <div class="bg01_top">
                <div class="arrow01"></div>
                <div class="bg01_txt">
                    <p class="bg01_tit">礼物冠名权</p>
                    <p class="bg01_txt">【贡献榜为1000钻、魅力榜为500钻、礼物榜为 100钻、情侣榜为10钻】</p>
                </div>
            </div>

            <ul class="bg01_bottom">
                <li>
                    <div class="arrow01"></div>
                    <div class="bg01_prize">
                        <img :src="prize01" class="prize" alt="">
                        <p>【九尾妖狐】 </p>
                        <p> 座驾7天</p>

                    </div>
                </li>
                <li>
                    <div class="arrow01"></div>
                    <div class="bg01_prize">
                        <img :src="prize02" class="prize" alt="">
                        <p>【豪华游艇】 </p>
                        <p> 赠送权*1</p>

                    </div>

                </li>

            </ul>
        </div>
    </div>
    <!--本周礼物上新-->
    <div class="weekly_main">
        <img :src="title02" class="title02" alt="">
        <div class="bg02">
            <ul class="prize_list">
                {% for gift in gifts %}
                    <li>
                        <img src="{{ gift.image_small_url }}" alt="">
                    </li>
                {% endfor %}
            </ul>
        </div>
        <!--上周榜单TOP1-->
        <img :src="title03" class="title03" alt="">
        <div class="bg03">
            <ul class="lastweekly_list">
                {% if last_week_charm_rank_list_user %}
                    <li>
                        <img src="/m/images/list_title01.png" alt="" class="list_icon"/>
                        <div class="list_info">
                            <img src="{{ last_week_charm_rank_list_user['avatar_url'] }}" class="list_avatar" alt="">
                            <p>{{ last_week_charm_rank_list_user['nickname'] }}</p>
                        </div>
                    </li>
                {% endif %}
                {% for index, last_gift in last_gifts %}
                    {% if index < 3 %}
                        <li>
                            {% if last_activity_rank_list_users %}
                                <img src="/m/images/list_title0{{ index+2 }}.png" class="list_icon" alt="">
                                <div class="list_info">
                                    {#暂时用礼物的信息#}
                                    <img src="{{ last_activity_rank_list_users[index]['avatar_url'] }}"
                                         class="list_avatar" alt="">
                                    <p>{{ last_activity_rank_list_users[index]['nickname'] }}</p>
                                </div>
                            {% endif %}
                        </li>
                    {% endif %}
                {% endfor %}
            </ul>
        </div>
    </div>
    <!--本周榜单TOP10-->
    <div class="weekly_footer">
        <img :src="title04" class="title04" alt="">
        <div class="remaining_time" v-show="!clock" id="time_text">
            <span> 剩余时间： </span>
            <div class="count_down">
                <span v-text="day"></span>
                <span> 天 </span>
                <span v-text="hr"></span>
                <span>:</span>
                <span v-text="min"></span>
                <span>:</span>
                <span v-text="sec"></span>
            </div>
        </div>
        {% if time() >= activity.start_at %}
            <div class="bg04">
                <ul class="tabs">
                    <li v-for="item,index in tabs" :class="{'cur':curIdx==index}" @click="tabSelect(index)">
                        <span v-text="item"></span>
                    </li>
                </ul>
                <div class="tabs_tips">
                    <span v-show="curIdx==0">贡献榜为用户送出礼物的周总值进行排名</span>
                    <span v-show="curIdx==1">魅力榜为用户收到礼物的周总值进行排名</span>
                    <span v-show="curIdx==2">礼物榜为用户收到本周新礼物的周总值进行排名</span>
                    <span v-show="curIdx==3">情侣榜为情侣互相赠送的礼物周总值进行排名</span>
                </div>
                <div class="myself">
                    <img class="myself_avatar" src="{{ current_user['avatar_url'] }}" alt="">
                    <div class="myself_text">
                        <div class="myself_name">
                            <span>{{ current_user['nickname'] }}</span>
                        </div>
                        <div class="myself_info" v-show="curIdx==0">
                            <p>贡献榜排名：<span class="highlight" v-text="current_user_info['current_rank_text']"></span>名</p>
                            <p>贡献值：<span class="highlight"
                                         v-text="current_user_info['current_score']?current_user_info['current_score']:0"></span>分
                            </p>
                        </div>
                        <div class="myself_info" v-show="curIdx==1">
                            <p>魅力榜排名：<span class="highlight" v-text="current_user_info['current_rank_text']"></span>名</p>
                            <p>魅力值：<span class="highlight"
                                         v-text="current_user_info['current_score']?current_user_info['current_score']:0"></span>分
                            </p>
                        </div>
                        <div class="myself_info" v-show="curIdx==2">
                            <p>礼物榜排名：<span class="highlight" v-text="current_user_info['current_rank_text']"></span>名</p>
                            <p>礼物值：<span class="highlight"
                                         v-text="current_user_info['current_score']?current_user_info['current_score']:0"></span>分
                            </p>
                        </div>
                        <div class="myself_info" v-show="curIdx==3">
                            <p>情侣榜排名：<span class="highlight" v-text="current_user_info['current_rank_text']"></span>名</p>
                            <p>情侣值：<span class="highlight"
                                         v-text="current_user_info['current_score']?current_user_info['current_score']:0"></span>分
                            </p>
                        </div>

                    </div>
                </div>
                <ul class="rank_list" v-show="curIdx!=3">
                    <li v-for="(user,index) in users"
                        :class="[index==0?'rank_first':''|| index==1?'rank_second':''|| index==2?'rank_third':'']">
                        <div class="rank_info">
                            <div class="rank_num">
                                <span v-text="index+1"></span>
                            </div>
                            <div class="rank_avatar_bg">
                                <img class="rank_avatar" :src="user.avatar_url" alt="">
                            </div>

                            <div class="rank_name">
                                <span v-text="user.nickname"></span>
                            </div>
                        </div>
                    </li>
                </ul>

                <ul class="lover_list" v-show="curIdx==3">
                    <li v-for="(cp_user,index) in cp_users">
                        <div class="lover_info">
                            <div class="lover_num">
                                <span v-text="index+1" v-if="index>2"></span>
                                <img class="lover_number" :src="lover_num[index]" alt="" v-if="index<3">
                            </div>
                            <img class="lover_avatar" :src="cp_user[0]['avatar_url']" alt="">
                            <div class="lover_name">
                                <span v-text="cp_user[0]['nickname']"></span>
                            </div>
                            <img class="lover_avatar" :src="cp_user[1]['avatar_url']" alt="">
                            <div class="lover_name">
                                <span v-text="cp_user[1]['nickname']"></span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        {% endif %}
        <div class="footer">
            <span>- 活动最终解释权归HI语音官方团队 -</span>
        </div>

    </div>


    <div class="mask" v-show="showRule">
        <div class="rule_box" :class="{'slidup':showRule}">
            <ul class="rule_list">
                <li>
                    <span class="rule_num">1</span>
                    <p>所有冠名权的时限均为 <span class="highlight">一周</span></p>
                </li>
                <li>
                    <span class="rule_num">2</span>
                    <p>榜单时间为<span class="highlight">每周一17:00—周日24:00</span></p>
                </li>
                <li>
                    <span class="rule_num">3</span>
                    <p>
                        新礼物冠名请于每周一上午14:00前提交官方QQ：3407150190，逾时按获奖ID昵称作为冠名内容
                    </p>
                </li>
                <li>
                    <span class="rule_num">4</span>
                    <p>活动结果将于<span class="highlight">每周一12:00</span>公布，请保持关注</p>
                </li>
                <li>
                    <span class="rule_num">5</span>
                    <p>每个榜单的冠军奖励均为新礼物冠名权、【九尾妖狐】座驾7天、绝版礼物【豪华游艇】赠送权*1（情侣榜二人均可获得礼物及座驾）</p>
                </li>
            </ul>
            <div class="rule_right">- 活动最终解释权归Hi语音官方团队 -</div>


        </div>
        <img :src="rule_close" class="rule_close" :class="{'slidup':showRule}" alt="" @click="ruleHide">

    </div>
</div>
<script>

    var opts = {
        data: {
            bg_header: '/m/images/bg_header.png',
            bg_main: '/m/images/bg_main.png',
            bg_footer: '/m/images/bg_footer_2.png',
            title01: '/m/images/title01.png',
            title02: '/m/images/title02.png',
            title03: '/m/images/title03.png',
            title04: '/m/images/title04.png',
            bg01: '/m/images/bg01.png',
            bg02: '/m/images/bg02.png',
            bg03: '/m/images/bg03.png',
            bg04: '/m/images/bg04.png',
            prize01: '/m/images/prize01.png',
            prize02: '/m/images/prize02.png',
            showRule: false,
            rule_btn: '/m/images/rule_btn.png',
            rule_close: '/m/images/rule_close.png',
            second: '',
            day: '',
            hr: '',
            min: '',
            sec: '',
            clock: false,
            end_time: '2018/05/28 12:00',
            tabs: ['贡献榜', '魅力榜', '礼物榜', '情侣榜'],
            tabs_types: ['wealth', 'charm', 'total_gifts', 'cp'],
            curIdx: 0,
            myself: {
                contribute_highest: 12,
                contribute_value: 98,
                charm_highest: 13,
                charm_value: 1980,
                prize_highest: 32,
                prize_value: 988,
                lovers_highest: 23,
                lovers_value: 198,
                avatar: '/m/images/prize01.png',
                name: '玩家名称',
            },
            users: [],
            cp_users: [],
            lover_num: ['/m/images/lover_num0.png', '/m/images/lover_num1.png', '/m/images/lover_num2.png'],
            current_user_info: []

        },
        created: function () {
            this.getUsers('wealth');
        },
        methods: {
            getCpUsers: function (type) {
                var data = {
                    sid: '{{ sid }}',
                    code: '{{ code }}',
                    id: '{{ id }}',
                    type: type
                };
                $.authGet('/m/activities/get_current_activity_cp_rank_list', data, function (resp) {
                    vm.cp_users = [];
                    if (resp.error_code == 0) {
                        $.each(resp.users, function (index, item) {
                            vm.current_user_info = resp.current_user_cp_info;
                            vm.cp_users.push(item);
                        });
                    }
                });
            },
            getUsers: function (type) {
                var data = {
                    sid: '{{ sid }}',
                    code: '{{ code }}',
                    id: '{{ id }}',
                    type: type
                };
                $.authGet('/m/activities/get_current_activity_rank_list', data, function (resp) {
                    vm.users = [];
                    console.log(resp);
                    if (resp.error_code == 0) {
                        $.each(resp.users, function (index, item) {
                            vm.current_user_info = resp.current_user_info;
                            vm.users.push(item);
                        });
                    }
                });
            },
            tabSelect: function (index) {
                this.curIdx = index;
                var type = this.tabs_types[index];
                if (type != 'cp') {
                    this.getUsers(type);
                } else {
                    this.getCpUsers(type);
                }
            },
            ruleShow: function () {
                this.showRule = true
            },
            ruleHide: function () {
                this.showRule = false
            },
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
{{ block_begin('head') }}
{{ theme_css('/m/css/gift_week_20180604_rank_activity.css') }}
{{ theme_js('/m/js/resize.js') }}
{{ block_end() }}
<div class="vueBox" id="app" v-cloak>
    <img :src="banner" class="banner" alt="">
    <!--规则详情-->
    <img :src="rule_btn" class="rule_btn" alt="" @click="ruleShow">
    <!--本周新礼物-->
    <div class="new_prize" @click="newShow">
        <span>本周</span>
        <span>新礼物</span>
    </div>


    <div class="weekly_main">
        <!--上周榜单TOP1-->
        <div class="title">
            <span class="title_text">上周榜单TOP1</span>
            <div class="bubble"></div>
        </div>
        <div class="lastweekly_top10">
            <ul class="lastweekly_list">
                <li>
                    <div class="list_tit">贡献榜</div>
                    <div class="list_info">
                        <img :src="last_activity_users['wealth']['avatar_url']" class="list_avatar" alt="">
                        <p v-text="last_activity_users['wealth']['nickname']"></p>
                    </div>
                </li>
                <li>
                    <div class="list_tit">魅力榜</div>
                    <div class="list_info">
                        <img :src="last_activity_users['charm']['avatar_url']" class="list_avatar" alt="">
                        <p v-text="last_activity_users['charm']['nickname']"></p>
                    </div>
                </li>
                <li>
                    <div class="list_tit">礼物榜</div>
                    <div class="list_info">
                        <img :src="last_activity_users['total_gifts']['avatar_url']" class="list_avatar" alt="">
                        <p v-text="last_activity_users['total_gifts']['nickname']"></p>
                    </div>
                </li>
                <li>
                    <div class="list_tit">情侣榜</div>
                    <div class="list_info">
                        <div class="list_avatars">
                            <img :src="last_activity_users['cp'][0]['avatar_url']" class="list_avatar1" alt="">
                            <img :src="last_activity_users['cp'][1]['avatar_url']" class="list_avatar2" alt="">
                        </div>
                        <p>${last_activity_users['cp'][0]['nickname']}</p>
                        <p>${last_activity_users['cp'][1]['nickname']}</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <!--本周榜单TOP10-->
    <div class="weekly_footer">
        <!--本周榜单-->
        <div class="title">
            <span class="title_text">本周榜单</span>
        </div>
        <img :src="green_leaf" class="green_leaf" alt="">
        <img :src="bubble_two" class="bubble_two" alt="">


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

        <div class="bg04">
            <img :src="orange_slice" class="orange_slice" alt="">
            <ul class="tabs">
                <li v-for="item,index in tabs" :class="{'cur':curIdx==index}" @click="tabSelect(index)">
                    <span v-text="item"></span>
                </li>
            </ul>
            <div class="tabs_tips">
                <span v-show="curIdx==0">贡献为用户送出礼物的总值排名</span>
                <span v-show="curIdx==1">魅力榜为用户收到礼物的周总值进行排名</span>
                <span v-show="curIdx==2">礼物榜为用户收到本周新礼物的周总值进行排名</span>
                <span v-show="curIdx==3">情侣榜为情侣相互赠送礼物的周总值进行排名</span>
            </div>

            <div class="champion_award">
                <div class="award_title" v-if="curIdx==2">
                    <span>本周上新</span>
                </div>
                <ul class="new_list" v-if="curIdx==2">
                    {% for gift in gifts %}
                    <li>
                        <img src="{{ gift.image_small_url }}" class="ico_prize" alt="">
                        <span>{{ gift.name }}</span>
                        {% endfor %}
                </ul>

                <div class="award_title">
                    <span v-show="curIdx==0">贡献榜冠军奖励</span>
                    <span v-show="curIdx==1">魅力榜冠军奖励</span>
                    <span v-show="curIdx==2">礼物榜冠军奖励</span>
                    <span v-show="curIdx==3">情侣榜冠军奖励</span>
                </div>
                <div class="award_title">
                    <span v-show="curIdx==3" style="font-size: small">（除冠名礼物外情侣每人一份）</span>
                </div>
                <ul class="award_list" v-show="curIdx==0">
                    <li v-for="(item,index) in awardList">
                        <img :src="item.ico" class="award_img" alt="">
                        <span v-text="index==0?'1000'+item.txt:item.txt"></span>
                    </li>
                </ul>
                <ul class="award_list" v-show="curIdx==1">
                    <li v-for="(item,index) in awardList">
                        <img :src="item.ico" class="award_img" alt="">
                        <span v-text="index==0?'500'+item.txt:item.txt"></span>
                    </li>
                </ul>
                <ul class="award_list" v-show="curIdx==2">
                    <li v-for="(item,index) in awardList">
                        <img :src="item.ico" class="award_img" alt="">
                        <span v-text="index==0?'100'+item.txt:item.txt"></span>
                    </li>
                </ul>
                <ul class="award_list" v-show="curIdx==3">
                    <li v-for="(item,index) in awardList">
                        <img :src="item.ico" class="award_img" alt="">
                        <span v-text="index==0?'10'+item.txt:item.txt"></span>
                    </li>
                </ul>
            </div>
            {% if time() >= activity.start_at %}
            <ul class="rank_list" v-show="curIdx!=3">
                <li v-for="(user,index) in users"
                    :class="[index==0?'rank_first':''|| index==1?'rank_second':''|| index==2?'rank_third':'']">
                    <div class="rank_info">
                        <div class="rank_num">
                            <span v-text="index+1"></span>
                        </div>
                        <div class="rank_avatar_bg">
                            <img class="rank_avatar" :src="user.avatar_url" alt="">
                            <img class="rank_avatar_border" v-if="index==0" :src="rank_first" alt="">
                        </div>
                        <div class="rank_name">
                            <span v-text="user.nickname"></span>
                        </div>
                    </div>
                </li>
            </ul>
            <ul class="lover_list" v-show="curIdx==3">
                <li v-for="(cp_user,index) in cp_users">
                    <div :class="['lover_info', index==0?'lover_info1':''|| index==1?'lover_info2':''|| index==2?'lover_info3':'']">

                        <div class="lover_num" :class="">
                            <span v-text="'NO.'+(index+1)"></span>
                        </div>

                        <div class="lover_imgs">
                            <img class="lover_avatar" :src="cp_user[0]['avatar_url']" alt="">
                            <img class="lover_heart" :src="index<3?lover_heart[index]:lover_heart[3]" alt="">
                            <img class="lover_avatar" :src="cp_user[1]['avatar_url']" alt="">
                        </div>

                        <div class="lover_names">
                            <span class="lover_name" v-text="cp_user[0]['nickname']"></span>
                            <span class="symbol_and" v-text="index?'&':''"></span>
                            <span class="lover_name" v-text="cp_user[1]['nickname']"></span>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="myself_lover" v-show="curIdx==3">
                <div class="myself" v-if="current_user_info">
                    <div class="lover_imgs">
                        <img class="lover_avatar" src="{{ current_user['avatar_url'] }}" alt="">
                        <img class="lover_heart" :src="lover_heart[3]" alt="">
                        <img class="lover_avatar" :src="current_user_info['other_user_avatar_url']" alt="">
                    </div>
                    <div class="lover_names">
                        <span class="lover_name">{{ current_user['nickname'] }}</span>
                        <span class="symbol_and">&</span>
                        <span class="lover_name" v-text="current_user_info['other_user_nickname']"></span>
                    </div>
                </div>
                <div class="myself_info" v-show="curIdx==3" v-if="current_user_info">
                    <p>情侣榜排名：<span class="highlight" v-text="current_user_info['current_rank_text']"></span>名</p>
                    <p>情侣值：<span class="highlight"
                                 v-text="current_user_info['current_score']"></span>分
                    </p>
                </div>
                <div class="myself_info" v-show="curIdx==3" v-if="!current_user_info">
                    <p>暂无数据</p>
                </div>
            </div>

            <div class="myself" v-show="curIdx<3">
                <img class="myself_avatar" src="{{ current_user['avatar_url'] }}" alt="">
                <div class="myself_text">
                    <div class="myself_name">
                        <span>{{ current_user['nickname'] }}</span>
                    </div>
                    <div class="myself_info" v-show="curIdx==0">
                        <p>贡献榜排名：<span class="highlight" v-text="current_user_info['current_rank_text']"></span>名
                        </p>
                        <p>贡献值：<span class="highlight"
                                     v-text="current_user_info['current_score']"></span>分
                        </p>
                    </div>
                    <div class="myself_info" v-show="curIdx==1">
                        <p>魅力榜排名：<span class="highlight" v-text="current_user_info['current_rank_text']"></span>名
                        </p>
                        <p>魅力值：<span class="highlight"
                                     v-text="current_user_info['current_score']"></span>分
                        </p>
                    </div>
                    <div class="myself_info" v-show="curIdx==2">
                        <p>礼物榜排名：<span class="highlight" v-text="current_user_info['current_rank_text']"></span>名
                        </p>
                        <p>礼物值：<span class="highlight"
                                     v-text="current_user_info['current_score']"></span>分
                        </p>
                    </div>

                </div>
            </div>
            {% endif %}
        </div>

        <div class="footer">
            <span>- 活动最终解释权归HI语音官方团队 -</span>
            <img :src="bubble_two" class="footer_bubble_two" alt="">
        </div>

    </div>

    <div class="mask" v-show="showRule">
        <div class="rule_box " :class="{'slidup':showRule}">
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
                        新礼物冠名权请于每周一14：00前提交官方 QQ：3407150190，逾时按获奖ID昵称作为冠名内容
                    </p>
                </li>
                <li>
                    <span class="rule_num">4</span>
                    <p>活动结果将于<span class="highlight">每周一12:00</span>公布，请保持关注</p>
                </li>
            </ul>
            <div class="rule_right">- 活动最终解释权归Hi语音官方团队 -</div>
            <img :src="rule_close" class="rule_close" :class="{'slidup':showRule}" alt="" @click="ruleHide">

        </div>


    </div>
    <div class="mask" v-show="showNew">
        <div class="new_box" :class="{'slidup':showNew}">
            <!--本周礼物上新-->
            <ul class="prize_list">
                {% for gift in gifts %}
                    <li>
                        <img src="{{ gift.image_small_url }}" class="prize" alt="">
                        <span>{{ gift.name }}</span>
                    </li>
                {% endfor %}
            </ul>
            <img :src="rule_close" class="rule_close" :class="{'slidup':showRule}" alt="" @click="newHide">

        </div>


    </div>
</div>
<script>

    var opts = {
        data: {
            current_user_info: [],
            rank_first: '/m/images/rank_first_2.png',
            banner: '/m/images/banner_0604.png',
            green_leaf: '/m/images/green_leaf.png',
            orange_slice: '/m/images/orange_slice.png',
            bubble_two: '/m/images/bubble_two.png',
            last_activity_users:{{ last_activity_users }},
            users: [],
            cp_users: [],
            awardList: [/*冠军礼物*/
                {
                    txt: '钻礼物冠名权',
                    ico: '/m/images/award01.png',
                },
                {
                    txt: '【小飞猪】座驾15天',
                    ico: '/m/images/pig_icon.png',
                },
                {
                    txt: '【女神跑车】赠送权*1',
                    ico: '/m/images/award03.png',
                }

            ],

            showRule: false,
            showNew: false,
            rule_btn: '/m/images/rule_btn.png',
            rule_box_title: '/m/images/rule_box_title.png',
            rule_close: '/m/images/ico_close.png',
            second: '',
            day: '',
            hr: '',
            min: '',
            sec: '',
            clock: false,
            end_time: '2018/05/31 12:00',
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
                avatar: '/m/images/gift_icon01.png',
                name: '玩家名称',
                cp_avatar: '/m/images/gift_icon01.png',
                cp_name: '玩家名称',
            },
            lover_heart: ['/m/images/lover_heart01.png', '/m/images/lover_heart02.png', '/m/images/lover_heart03.png', '/m/images/lover_heart04.png',],
        },
        created: function () {
            this.getUsers('wealth');
        },
        methods: {
            newShow: function () {
                this.showNew = true
            },
            newHide: function () {
                this.showNew = false
            },
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

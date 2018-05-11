{{ block_begin('head') }}
{{ theme_js('/m/js/resize.js','/m/js/room_password_pop') }}
{{ theme_css('/m/activities/css/karaoke_master.css','/m/css/room_password_pop') }}
{{ block_end() }}
<div class="vueBox" id="app">
    <img class="bg" src="images/bg.png" alt="">
    <div class="header">
        <img class="headerbg" src="images/headerbg.png" alt="">
    </div>
    <div class="room_box">
        <img class="roombg" src="images/roombg.png" alt="">
        <div class="room_id" v-text="room.id"></div>
        <div class="room_sponsor" v-text="room.sponsor"></div>
    </div>


    <div class="prizes_box">
        <img class="prizes_bg" src="images/prizes_bg.png" alt="">
        <div class="prizes_title">
            <span class="prizes_title_txt">奖品</span>
        </div>
        <ul class="prizes_list">
            <li v-for="prize in prizes">
                <p class="prize_tit" v-text="prize.tit"></p>
                <p class="prize_txt" v-text="prize.txt"></p>
                <p class="prize_tip" v-text="prize.tip" v-if="prize.tip"></p>
                <p v-if="prize.info" class="prize_info_box">
                    <span class="prize_info" v-text="prize.info"></span>
                    <span class="prize_bubble" v-text="prize.bubble"></span>
                </p>
            </li>
        </ul>
    </div>

    <div class="sign_box">
        <img class="sign_bg" src="images/sign_bg.png" alt="">
        <div class="sign_title">
            <span>报名详情</span>
        </div>
        <div class="sign_tit">报名时间</div>
        <div class="sign_time" v-text="sign.time"></div>
        <div class="sign_tit">报名方式</div>
        <div class="sign_time" v-text="sign.consult"></div>
        <div class="sign_way">
            <ul class="way_list">
                <li v-for="way in sign.way">
                    <img class="sign_ico" :src="way.ico" alt="">
                    <div class="sign_info">
                        <p class="way_tit" v-text="way.tit"></p>
                        <p class="way_text">
                            <span class="way_txt" v-text="way.txt"></span>
                            <span class="way_tip" v-text="way.tip"></span>
                        </p>
                    </div>
                </li>
            </ul>
        </div>
        <div class="sign_tit">比赛时间</div>
        <div class="sign_step">
            <ul class="step_left">
                <li>
                    <p class="step_time">2018年5月20日</p>
                    <p class="step_title">海选</p>
                </li>
                <li class="step2">
                    <p class="step_time">2018年5月22日</p>
                    <p class="step_title">第二轮</p>
                </li>
            </ul>
            <ul class="step_right">
                <li class="step1">
                    <p class="step_title">第一轮</p>
                    <p class="step_time">2018年5月21日</p>
                </li>
                <li class="step4">
                    <p class="step_title">
                        半决赛<br>总决赛</p>
                    <p class="step_time">2018年5月26日</p>
                </li>
            </ul>
        </div>
        <div class="contest_time">
            <img class="ico_time" src="images/ico_time.png" alt="">
            <span>比赛时间</span>
            <span>每天晚上20：00</span>
        </div>
        <div class="contest_time_tips">（需比赛前30分钟内到达）</div>

    </div>

    <div class="btn_tips" @click="navToDetails">
        <span>具体比赛规则</span>
        <img class="ico_arrow" src="images/ico_arrow.png" alt="">
    </div>
    <div class="btn" @click="karaokeMasterApply()">
        <img class="btn_bg" src="images/btn_bg.png" alt="">
        <span>点击报名</span>
    </div>
    <div class="share_box" @click="openShare()">
        <img class="btn_share" src="images/btn_share.png" alt="">
        <span>分享</span>
    </div>
    <!-- 分享 -->
    <div v-if="isShareToast" class="share_toast">
        <ul class="share_toast_ul">
            <li @click="share('wx_friend','web_page')"><img src="/m/images/weixin_icon.png" alt=""/><span>微信</span></li>
            <li @click="share('wx_moments','web_page')"><img src="/m/images/friends_icon.png" alt=""/><span>朋友圈</span>
            </li>
            <li @click="share('qq_friend','web_page')"><img src="/m/images/qq_icon.png" alt=""/><span>QQ</span></li>
            <li @click="share('qq_zone','web_page')"><img src="/m/images/kongjian_icon.png" alt=""/><span>QQ空间</span>
            </li>
            <li @click="share('sinaweibo','web_page')"><img src="/m/images/weibo_icon.png" alt=""/><span>微博</span></li>
        </ul>
        <span @click="cancelShare()" class="cancel">取消</span>
    </div>
    {#密码弹框#}
    <div class="room_cover">
        <div class="room_pop">
            <img class="room_pop_bg" src="/m/images/room_pop_bg.png" alt="">
            <div class="room_locked">房间已上锁</div>
            <div class="room_lock">
                <label for="">密码</label>
                <input class="input_text" maxlength="10" type="number" placeholder="最多输入10个字" id="password" style="
    background-color: #F0F0F0;">
            </div>
            <div class="room_btn">
                <span class="room_out">取消</span>
                <span class="room_in">进入房间</span>
            </div>
        </div>
    </div>

</div>
<script>
    sid = "{{ sid }}";
    code = "{{ code }}";
    var opts = {
        data: {
            isShareToast: false,
            room: {
                id: '房主ID：1009978',
                sponsor: '(本次比赛由SH.恋爱家族主办）'
            },
            prizes: [
                {
                    tit: '第一名',
                    txt: '现金999元+9999钻石+猎影15天',
                    info: '官方主办冠军专属演唱会',
                    bubble: '顶级资源位'
                },
                {
                    tit: '第二名',
                    txt: '现金600元+6666钻石+天马15天'
                },
                {
                    tit: '第三名',
                    txt: '现金300 元+3344钻石+光电游侠15天'
                },
                {
                    tit: '第四名',
                    txt: '现金 99元+1314钻石+兰博基尼15天'
                },
                {
                    tit: '入围奖',
                    txt: '1000钻石10000金币',
                    tip: '（第五名—第十名）'
                },
                {
                    tit: '最佳人气奖一名',
                    txt: '现金200元+3344钻石+20000金币'
                },
                {
                    tit: '吃瓜群众奖',
                    txt: '本次大赛组委会将在每轮比赛随机抽取观众送出钻石大礼',
                    tip: '(998、488、198、118、68等随机奖励）'
                }
            ],
            sign: {
                time: '2018年5月11日00:00—5月19日23:59结束',
                consult: '每日报名咨询时间10:00—18:00',
                way: [
                    {
                        ico: 'images/ico_qq.png',
                        tit: 'QQ报名',
                        txt: '添加QQ1574139797',
                        tip: '(备注报名）'
                    },
                    {
                        ico: 'images/ico_weChat.png',
                        tit: '微信报名',
                        txt: "添加微信号ai1574139797或kaolaedu365",
                        tip: '(备注报名）'
                    },
                    {
                        ico: 'images/ico_hi.png',
                        tit: 'Hi语音报名',
                        txt: '房主ID:1162288',
                        tip: '(备注报名）'
                    }
                ]
            },
            room_id: "{{ room_id }}"
        },
        created: function () {

        },
        methods: {
            navToDetails: function () {
                var url = "/m/activities/details?sid={{ sid }}&code={{ code }}";
                window.location.href = url
            },
            karaokeMasterApply: function () {
                var data = {room_id: vm.room_id, sid: sid, code: code};
                $.authPost("/m/unions/is_need_password", data, function (resp) {
                    if (resp.error_code == 0) {
                        selected_room_id = vm.room_id;
                        $('.room_cover').show();
                    } else {
                        location.href = "app://rooms/detail?id=" + vm.room_id;
                    }
                });
            },
            openShare: function () {
                vm.isShareToast = true;
            },
            cancelShare: function () {
                vm.isShareToast = false;
            },
            share: function (platform, type) {
                var data = {
                    code: code,
                    sid: sid,
                    platform: platform,
                    type: type,
                    share_source: 'match_sing'
                };

                $.authPost('/m/shares/create', data, function (resp) {
                    vm.redirect_url = resp.test_url;
                    location.href = vm.redirect_url;
                })

            },
        }
    };
    vm = XVue(opts);
</script>
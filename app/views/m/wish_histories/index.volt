{{ block_begin('head') }}
{{ theme_css('/m/css/wish_main.css') }}
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
<div id="app" class="wishing_tree">
    <div @click="openToast(1)" class="wishing_rules">
        <span>活动规则</span>
    </div>
    <div class="wishing_rules wishing_record" @click="toA()">
        <span>中奖纪录</span>
    </div>
    <span @click="onPaly" class="wishing_music" :class="[!isPaly&&'wishing_music_pause']"></span>
    <span @click="openToast(2)" class="wishing_share"></span>
    <div class="wishing_bouttom_box">
        <div class="ranking" @click="toRanking"></div>
        <div @click="openToast(3)" class="makevow"></div>
        <div class="refresh" @click="refresh()"></div>
    </div>
    <!-- 规则弹窗 -->
    <div v-if="isrulesToast" class="wishing_rules_toast">
        <div class="wishing_rules_scroll">
            <ul>
                <li><span class="dot"></span> 每个用户可发布3个愿望，若最终中奖只能兑现一个</li>
                <li><span class="dot"></span> 您可为他人的愿望赠送守护之心，每颗守护之心将消费2钻</li>
                <li><span class="dot"></span> 活动期间后台将每天不定时抽取5名参与者送豪礼，直至活动结束，抽到的幸运儿将有机会获得价值3000元的机械表（1块），共三款靓表供您选择</li>
                <li><span class="dot"></span> 活动结束后，将从所有参与者中随机抽取10名幸运儿为其实现力所能及的愿望；原则上愿望内容价值不得超过10W钻</li>
                <li><span class="dot"></span> 本活动目的重在参与，后台将随机抽取30名用户送1000钻，参与用户包括许愿者及赠送守护之心的用户</li>
                <li><span class="dot"></span> 参与者许下的愿望将经过审核后方可上墙；许愿内容不允许出现色情暴力，人身攻击等不文明言语，如若出现，平台将有权不予上许愿墙</li>
                <li><span class="dot"></span> 活动开始时间5.13零点开始至6.17二十四点止；另对于中奖者，官方后台将会主动联系</li>
                <li><span class="dot"></span> 本活动最终解释权归Hi语音所有</li>
            </ul>
            <span class="wire"></span>
        </div>
        <span @click="onCancelToast(1)" class="cancel_buttom"></span>
    </div>
    <!-- 发布愿望弹窗 -->
    <div v-if="releaseWish" class="release_wish">
        <ul>
            <li>
                <textarea placeholder="✏️许下一个小小的愿望，万一实现了呢…" @input="descInput" v-model="my_wish_text"></textarea>
                <span class="text_length">{{remnant}}/100</span>
                <div class="release_wish_buttom" @click="myReleaseWish"></div>
                <span @click="onCancelToast(3)" class="cancel_release"></span>
            </li>
            <li v-for="my_wish_data,index in my_wish_datas">
                <div class="release_wish_heart"><span class="heart_icon"></span><span>X${my_wish_data[0] ? my_wish_data[0] : 0}</span> </div>
                <p>${my_wish_data[1]}</p>
            </li>
        </ul>
        {#<textarea v-if="releaseWishState==1" placeholder="✏️许下一个小小的愿望，万一实现了呢…" v-model="my_wish_text"></textarea>#}
        {#<div class="release_wish_buttom" @click="myReleaseWish"></div>#}



        <span @click="onCancelToast(3)" class="cancel"></span>
    </div>
    <!-- 我的愿望列表弹窗 -->
    <div v-if="myWishList" class="mywish_list">
        <ul>
            <li v-for="show_wish_history,index in show_wish_histories" >
                <div class="release_wish_heart">
                    <span class="heart_icon"></span>
                    <span id="guarded_number">X${show_wish_history.guarded_number?show_wish_history.guarded_number:0}</span>
                </div>
                <!-- 状态2查看别人愿望 -->
                <div class="release_wish_user">
                    <img :src="show_wish_history.user_avatar_url" alt="">
                    <div class="release_wish_user_name">
                        <span>昵称:${show_wish_history.user_nickname}</span>
                        <span>ID：${show_wish_history.user_uid}</span>
                    </div>
                </div>
                <p>${show_wish_history.wish_text}</p>
                <div @click="guardWish(show_wish_history.id,index)" class="release_wish_box">
                    <span class="release_wish_buttom2"></span>
                </div>
            </li>
            {#<li v-if="false" :class="[releaseWishState==2&&'background2']">#}
                {#<!-- 状态2查看别人愿望 -->#}
                {#<div v-if="releaseWishState==2" class="release_wish_user">#}
                    {#<img :src="show_wish_history.user_avatar_url" alt="">#}
                    {#<div class="release_wish_user_name">#}
                        {#<span>昵称:${show_wish_history.user_nickname}</span>#}
                        {#<span>ID：${show_wish_history.user_uid}</span>#}
                    {#</div>#}
                {#</div>#}
                {#<p>${releaseWishState==2?show_wish_history.wish_text:show_wish_history}</p>#}

                {#<!-- <p v-if="releaseWishState==2" class="release_wish_text">每个人的心目中都会有一些美好的愿望，我的心中梦寐以求的愿望便是养一只可爱的小白狗。因为，有一次在伊通河边我看到了一只小白狗，它毛茸茸的，全身雪白雪白的，样子非常可爱。</p> -->#}
                {#<div v-if="releaseWishState==2" class="release_wish_heart"><span#}
                            {#class="heart_icon"></span><span id="guarded_number">X${show_wish_history.guarded_number?show_wish_history.guarded_number:0}</span>#}
                {#</div>#}
                {#<div v-if="releaseWishState==2" class="release_wish_buttom2"#}
                     {#@click="guardWish(show_wish_history.id,index)"></div>#}
            {#</li>#}
        </ul>
        <span @click="onCancelToast(4)" class="cancel"></span>
    </div>
    <!-- 分享 -->
    <div v-if="isShareToast" class="wishing_share_toast">
        <ul class="wishing_share_toast_ul">
            <li @click="share('wx_friend','web_page','share_source')"><img src="/m/images/weixin_icon.png" alt=""/><span>微信</span></li>
            <li @click="share('wx_moments','web_page','share_source')"><img src="/m/images/friends_icon.png" alt=""/><span>朋友圈</span>
            </li>
            <li @click="share('qq_friend','web_page','share_source')"><img src="/m/images/qq_icon.png" alt=""/><span>QQ</span></li>
            <li @click="share('qq_zone','web_page','share_source')"><img src="/m/images/kongjian_icon.png" alt=""/><span>QQ空间</span>
            </li>
            <li @click="share('sinaweibo','web_page','share_source')"><img src="/m/images/weibo_icon.png" alt=""/><span>微博</span></li>
        </ul>
        <span @click="onCancelToast(2)" class="cancel">取消</span>
    </div>
    <!-- 余额不足 -->
    <div v-if="isHintToast" class="not_balance_toast">
        <b>提示</b>
        <span class="hint">您的钻石余额不足，请先充值</span>
        <div class="not_balance_box">
            <span @click="topupBalance(false)" class="cancel">取消</span>
            <span @click="topupBalance(true)" class="topup">充值</span>
        </div>
    </div>

    <!-- 背景遮罩 -->
    <div v-if="isShareToast||isrulesToast||releaseWish||myWishList||isHintToast" class="mask_background"></div>
    <audio id="playbgm" autoplay loop src="/m/css/59ffe5e4e8717.mp3"></audio>

    <div @click="showOthersWish" class="view_wish"></div>
</div>
<script>
    var opts = {
        data: {
            page: 1,
            isShareToast: false,
            isrulesToast: false,
            releaseWish: false,
            myWishList: false,
            isHintToast: false,
            isPaly: true,
            // 状态：1我的愿望、2查看他人愿望
            releaseWishState: 1,
            sid: "{{ sid }}",
            code: "{{ code }}",
            my_wish_text: '',
            show_wish_histories: [],
            my_wish_datas:[],
            is_guard: false,
            remnant: 0,
            desc:'',
        },
        mounted: function () {
        },
        methods: {
            descInput:function(){
                var txtVal = this.my_wish_text.length;
                if(txtVal<=100){
                    this.remnant = txtVal;
                }
            },
            onCancelToast: function (index) {
                switch (index) {
                    case 1:
                        vm.isrulesToast = false;
                        break;
                    case 2:
                        vm.isShareToast = false;
                        break;
                    case 3:
                        vm.releaseWish = false;
                        break;
                    case 4:
                        location.reload();
                        vm.myWishList = false;
                        break;
                }
            },
            openToast: function (index) {
                switch (index) {
                    case 1:
                        vm.isrulesToast = true;
                        break;
                    case 2:
                        vm.isShareToast = true;
                        break;
                    case 3:
                        myWishGuard();
                        break;
                    case 4:
                        myWishHistories();
                        break;
                }
            },
            showOthersWish: function () {
                vm.releaseWishState = 2;
                vm.myWishList = true;
            },
            onPaly: function () {
                vm.playbgm = document.getElementById('playbgm');
                if (vm.isPaly) {
                    vm.playbgm.pause();
                } else {
                    vm.playbgm.play();
                }
                vm.isPaly = !vm.isPaly;
            },
            myReleaseWish: function () {
                console.log(1);
                var data = {
                    sid: vm.sid,
                    code: vm.code,
                    my_wish_text: vm.my_wish_text

                };
                $.authPost('/m/wish_histories/release_wish', data, function (resp) {
                    if (!resp.error_code) {
                        vm.releaseWish = false;
                        location.reload();
                    }
                    alert(resp.error_reason);
                });
            },
            guardWish: function (wish_history_id, index) {
                var data = {
                    sid: vm.sid,
                    code: vm.code,
                    wish_history_id: wish_history_id
                };
                $.authPost('/m/wish_histories/guard_wish', data, function (resp) {
                    if (!resp.error_code) {
                        vm.is_guard = true;
                        vm.show_wish_histories[index].guarded_number = resp.guarded_number;
                    }
                })
            },
            share: function (platform, type,share_source) {
                var data = {
                    code: vm.code,
                    sid: vm.sid,
                    platform: platform,
                    type: type,
                    share_source:share_source
                };

                $.authPost('/m/shares/create', data, function (resp) {
                    vm.redirect_url = resp.test_url;
                    location.href = vm.redirect_url;
                })

            },
            toA:function () {
                location.href = '/m/wish_histories/winning_record?sid='+vm.sid+'&code='+vm.code
            },
            toRanking:function () {
                location.href = '/m/wish_histories/wishing_tree_list?sid='+vm.sid+'&code='+vm.code
            }
        }
    };
    vm = XVue(opts);
    refresh();

    function refresh() {
        var data = {
            sid: vm.sid,
            code: vm.code,
            page: vm.page
        };
        vm.page++;
        $.authPost('/m/wish_histories/refresh', data, function (resp) {
            if (!resp.error_code) {
                $.each(resp.wish_histories, function (index, item) {
                    vm.show_wish_histories.push(item);
                });
            } else {
                alert(resp.error_reason);
            }
        });
    }

    function myWishHistories() {
        var data = {
            sid: vm.sid,
            code: vm.code
        };
        $.authPost('/m/wish_histories/my_wish_histories', data, function (resp) {
            if (!resp.error_code) {
//                if (resp.my_wish_datas != '') {
                    vm.show_wish_histories = resp.my_wish_datas;
                    vm.releaseWishState = 1;
                    vm.myWishList = true;
//                }
            }
        });
    }

    function myWishGuard() {
        var data = {
            sid: vm.sid,
            code: vm.code
        };
        $.authPost('/m/wish_histories/my_wish_histories', data, function (resp) {
            if (!resp.error_code) {
//                if (resp.my_wish_datas != '') {
                    vm.my_wish_datas = resp.my_wish_datas;
                    vm.releaseWishState = 1;
                    vm.releaseWish = true;
//                }
            }
        });
    }
</script>
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
    <span @click="onPaly" class="wishing_music" :class="[!isPaly&&'wishing_music_pause']"></span>
    <span @click="openToast(2)" class="wishing_share"></span>
    <div class="wishing_bouttom_box">
        <div class="refresh" @click="refresh()"></div>
        <div @click="openToast(3)" class="makevow"></div>
        <div @click="openToast(4)" class="mywish_buttom"></div>
    </div>
    <!-- 规则弹窗 -->
    <div v-if="isrulesToast" class="wishing_rules_toast">
        <ul>
            <li><span class="dot"></span> 每发布一个愿望需要消耗5钻，发布个数不设限。</li>
            <li><span class="dot"></span> 你可以点开别人的愿望，如果有喜欢的愿望可以点击赠送守护之心（2钻）。</li>
            <li><span class="dot"></span> 活动结束后，官方将从守护之心数量最高的前五十名中，随机抽取三个实现愿望。</li>
            <li><span class="dot"></span> 本活动最终解释权归Hi语音所有。</li>
        </ul>
        <span @click="onCancelToast(1)" class="cancel_buttom"></span>
    </div>
    <!-- 发布愿望弹窗 -->
    <div v-if="releaseWish" class="release_wish">
        <textarea v-if="releaseWishState==1" placeholder="✏️许下一个小小的愿望，万一实现了呢…" v-model="my_wish_text"></textarea>
        <div class="release_wish_buttom" @click="myReleaseWish"></div>
        <span @click="onCancelToast(3)" class="cancel"></span>
    </div>
    <!-- 我的愿望列表弹窗 -->
    <div v-if="myWishList" class="mywish_list">
        <ul>
            <li v-for="show_wish_history,index in show_wish_histories" :class="[releaseWishState==2&&'background2']">
                <!-- 状态2查看别人愿望 -->
                <div v-if="releaseWishState==2" class="release_wish_user">
                    <img :src="show_wish_history.user_avatar_url" alt="">
                    <div class="release_wish_user_name">
                        <span>昵称:${show_wish_history.user_nickname}</span>
                        <span>ID：${show_wish_history.user_uid}</span>
                    </div>
                </div>
                <p>${releaseWishState==2?show_wish_history.wish_text:show_wish_history}</p>

                <!-- <p v-if="releaseWishState==2" class="release_wish_text">每个人的心目中都会有一些美好的愿望，我的心中梦寐以求的愿望便是养一只可爱的小白狗。因为，有一次在伊通河边我看到了一只小白狗，它毛茸茸的，全身雪白雪白的，样子非常可爱。</p> -->
                <div v-if="releaseWishState==2" class="release_wish_heart"><span
                            class="heart_icon"></span><span id="guarded_number">X${show_wish_history.guarded_number?show_wish_history.guarded_number:0}</span>
                </div>
                <div v-if="releaseWishState==2" class="release_wish_buttom2"
                     @click="guardWish(show_wish_history.id,index)"></div>
            </li>
        </ul>
        <span @click="onCancelToast(4)" class="cancel"></span>
    </div>
    <!-- 分享 -->
    <div v-if="isShareToast" class="wishing_share_toast">
        <ul class="wishing_share_toast_ul">
            <li @click="share('wx_friend','web_page')"><img src="/m/images/weixin_icon.png" alt=""/><span>微信</span></li>
            <li @click="share('wx_moments','web_page')"><img src="/m/images/friends_icon.png" alt=""/><span>朋友圈</span>
            </li>
            <li @click="share('qq_friend','web_page')"><img src="/m/images/qq_icon.png" alt=""/><span>QQ</span></li>
            <li @click="share('qq_zone','web_page')"><img src="/m/images/kongjian_icon.png" alt=""/><span>QQ空间</span>
            </li>
            <li @click="share('sinaweibo','web_page')"><img src="/m/images/weibo_icon.png" alt=""/><span>微博</span></li>
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
            wish_histories: [],
            my_wish_text: '',
            my_wish_datas: [],
            show_wish_histories: [],
            is_guard: false
        },
        mounted: function () {
        },
        methods: {
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
                        if (vm.releaseWishState == 2 && vm.is_guard) {
                            vm.is_guard = false;
                            location.reload();
                        }

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
                        vm.releaseWish = true;
                        break;
                    case 4:
                        myWishHistories();
                        vm.releaseWishState = 1;
                        vm.myWishList = true;
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
            share: function (platform, type) {
                var data = {
                    code: vm.code,
                    sid: vm.sid,
                    platform: platform,
                    type: type
                };

                $.authPost('/m/shares/create', data, function (resp) {
                    vm.redirect_url = resp.test_url;
                    location.href = vm.redirect_url;
                })

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
                    vm.wish_histories.push(item);
                });
                vm.show_wish_histories = vm.wish_histories;
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
                if (resp.my_wish_datas) {
                    vm.my_wish_datas = resp.my_wish_datas
                    vm.show_wish_histories = vm.my_wish_datas;
                } else {
                    alert('您还没有发起愿望，快去许愿吧');
                }
            }
        });
    }
</script>
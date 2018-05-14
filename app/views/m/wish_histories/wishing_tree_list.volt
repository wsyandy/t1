{{ block_begin('head') }}
{{ theme_css('/m/css/winning_record.css') }}
{{ theme_js('/m/js/wish_histories_index.js') }}
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
<div id="app" class="wishing_tree_list">
    <div class="wishing_list_neo">
        <span class="ranking_icon"></span>
        <div class="header">
            <span class="header_bg"></span>
            <img :src="wish_histories[0].user_avatar_url" alt="">
        </div>
        <div class="wishing_list_name"><span style="color: #F45189;">${wish_histories[0].user_nickname}</span><span
                    class="man"></span></div>
        <div class="wishing_list_heart">
            <span class="heart"></span>
            <span>X${wish_histories[0].guarded_number}</span>
        </div>
    </div>
    <div class="wishing_list_two">
        <span class="ranking_icon"></span>
        <div class="header">
            <span class="header_bg"></span>
            <img :src="wish_histories[1].user_avatar_url" alt="">
        </div>
        <div class="wishing_list_box">
            <div class="wishing_list_name"><span style="color: #56B1E3;">${wish_histories[1].user_nickname}</span><span
                        class="woman"></span></div>
            <div class="wishing_list_heart">
                <span class="heart"></span>
                <span>X${wish_histories[1].guarded_number}</span>
            </div>
        </div>
    </div>
    <div class="wishing_list_two">
        <span class="ranking_icon_three"></span>
        <div class="header">
            <span class="header_bg2"></span>
            <img :src="wish_histories[2].user_avatar_url" alt="">
        </div>
        <div class="wishing_list_box">
            <div class="wishing_list_name"><span style="color: #F37F32;">${wish_histories[2].user_nickname}</span><span
                        class="man"></span></div>
            <div class="wishing_list_heart">
                <span class="heart"></span>
                <span>X${wish_histories[2].guarded_number}</span>
            </div>
        </div>
    </div>
    {#</div>#}
    <ul class="wishing_list_ul">
        <li v-for="wish_history,index in wish_histories" v-if="index>2">
            <span class="ranking_icon">${index+1}</span>
            <div class="header">
                <img :src="wish_history.user_avatar_url" alt="">
            </div>
            <div class="wishing_list_box">
                <div class="wishing_list_name"><span>${wish_history.user_nickname}</span><span class="man"></span>
                </div>
                <div class="wishing_list_heart">
                    <span class="heart"></span>
                    <span>X${wish_history.guarded_number}</span>
                </div>
            </div>
        </li>
    </ul>
    <span @click="searchShow" class="search_button">搜索</span>
    <div class="my_ranking_toast">
        <span>暂未上榜</span>
    </div>
    <div v-if="searchToast" class="search_toast">
        <div class="search_toast_box">
            <span class="title">搜索</span>
            <input class="input_text" v-model="uid" placeholder="请输入查询ID" type="text"/>
            {#<p class="results">#}
            {#<span v-if="searchResults">昵称：黑芝麻</span>#}
            {#<span v-if="!searchResults" class="error">用户不存在</span>#}
            {#</p>#}
            <div class="search_toast_action">
                <span @click="searchConfrm(0)" class="cancel">取消</span>
                <span @click="searchConfrm(1)" class="confirm">确定</span>
            </div>
        </div>
    </div>
    <div v-if="myWishList" class="mywish_list">
        <ul>
            <li v-for="show_wish_history,index in show_wish_histories">
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
        </ul>
    </div>
    <span v-if="myWishList" @click="onCancelToast()" class="toast_cancel"></span>
    <div v-if="myWishList" class="mask_background"></div>
</div>
<script>
    var opts = {
        data: {
            uid: "",
            searchResults: true,
            searchToast: false,
            wish_histories:{{ wish_histories }},
            myWishList: false,
            show_wish_histories: [],
            sid: "{{ sid }}",
            code: "{{ code }}"
        },
        mounted: function () {

        },
        methods: {
            searchShow: function () {
                this.searchToast = true;
            },
            searchConfrm: function (index) {
                if (index) {
                    vm.searchToast = false;
                    var data = {
                        uid: this.uid,
                        sid: vm.sid,
                        code: vm.code,
                    };
                    console.log(data);
                    $.authPost('/m/wish_histories/search_user', data, function (resp) {
                        if (!resp.error_code) {
                            vm.myWishList = true;
                            vm.show_wish_histories = resp.show_wish_histories;
                        } else {
                            alert(resp.error_reason);
                        }
                    })
                } else {
                    vm.searchToast = false;
                }
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
            onCancelToast: function () {
                vm.myWishList = false;
            }

        }
    };

    vm = XVue(opts);
    $(function () {
        if (!vm.wish_histories) {
            alert('暂时还没有愿望哦！');
        }
    })
</script>

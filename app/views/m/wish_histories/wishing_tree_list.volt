{{ block_begin('head') }}
{{ theme_css('/m/css/winning_record.css') }}
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
<body>
<div id="app" class="wishing_tree_list">
    <div class="wishing_list_neo">
        <span class="ranking_icon"></span>
        <div class="header">
            <span class="header_bg"></span>
            <img src="{{ tree_list[0]['avatar'] }}" alt="">
        </div>
        <div class="wishing_list_name"><span style="color: #F45189;">{{ tree_list[0]['nickname'] }}</span><span
                    class="{{ tree_list[0]['sex'] }}">{{ tree_list[0]['age'] }}</span></div>
        <div class="wishing_list_heart">
            <span class="heart"></span>
            <span>X{{ tree_list[0]['guarded_number'] }}</span>
        </div>
    </div>
    <div class="wishing_list_two">
        <span class="ranking_icon"></span>
        <div class="header">
            <span class="header_bg"></span>
            <img src="{{ tree_list[1]['avatar'] }}" alt="">
        </div>
        <div class="wishing_list_box">
            <div class="wishing_list_name"><span style="color: #56B1E3;">{{ tree_list[1]['nickname'] }}</span><span
                        class="{{ tree_list[1]['sex'] }}">{{ tree_list[1]['age'] }}</span></div>
            <div class="wishing_list_heart">
                <span class="heart"></span>
                <span>X{{ tree_list[1]['guarded_number'] }}</span>
            </div>
        </div>
    </div>
    <div class="wishing_list_two">
        <span class="ranking_icon_three"></span>
        <div class="header">
            <span class="header_bg2"></span>
            <img src="{{ tree_list[2]['avatar'] }}" alt="">
        </div>
        <div class="wishing_list_box">
            <div class="wishing_list_name"><span style="color: #F37F32;">{{ tree_list[2]['nickname'] }}</span><span
                        class="{{ tree_list[2]['sex'] }}">{{ tree_list[2]['age'] }}</span></div>
            <div class="wishing_list_heart">
                <span class="heart"></span>
                <span>X{{ tree_list[2]['guarded_number'] }}</span>
            </div>
        </div>
    </div>
    <ul class="wishing_list_ul">
        {%  for index,val in tree_list %}
            {% if index > 2 %}
        <li>
            <span class="ranking_icon">{{ index+1 }}</span>
            <div class="header">
                <img src="{{ val['avatar'] }}" alt="">
            </div>
            <div class="wishing_list_box">
                <div class="wishing_list_name"><span>{{ val['nickname'] }}</span><span class="{{ val['sex'] }}">{{ val['age'] }}</span></div>
                <div class="wishing_list_heart">
                    <span class="heart"></span>
                    <span>X{{ val['guarded_number'] }}</span>
                </div>
            </div>
        </li>
            {% endif %}
        {% endfor %}
    </ul>
    <span @click="searchShow" class="search_button"></span>
    <div class="my_ranking_toast">
        <span>暂未上榜</span>
    </div>
    <div v-if="searchToast" class="search_toast">
        <div class="search_toast_box">
            <span class="title">搜索</span>
            <input class="input_text" v-model="input" placeholder="请输入查询ID" type="text"/>
            <p class="results">
                <span v-if="searchResults">昵称：黑芝麻</span>
                <span v-if="!searchResults" class="error">用户不存在</span>
            </p>
            <div class="search_toast_action">
                <span @click="searchConfrm(0)" class="cancel">取消</span>
                <span @click="searchConfrm(1)" class="confirm">确定</span>
            </div>
        </div>
    </div>
    <div v-if="myWishList" class="mywish_list">
        <ul>
            <li  >
                <div class="release_wish_heart">
                    <span class="heart_icon"></span>
                    <span id="guarded_number">X</span>
                </div>
                <!-- 状态2查看别人愿望 -->
                <div class="release_wish_user">
                    <img src="" alt="">
                    <div class="release_wish_user_name">
                        <span>昵称:</span>
                        <span>ID：</span>
                    </div>
                </div>
                <p></p>
                <div  class="release_wish_box">
                    <span class="release_wish_buttom2"></span>
                </div>
            </li>
        </ul>
        <span @click="onCancelToast()" class="cancel"></span>
    </div>
    <div v-if="myWishList" class="mask_background"></div>
</div>
<script>
    var opts = {
        el: '#app',
        data: {
            input:"",
            searchResults: true,
            searchToast: false,
            tree_list:{{ tree_list }},
            myWishList: false,
            searchToast:false,
        },
        mounted: function () {

        },
        methods: {
            searchShow: function () {
                this.searchToast = true;
            },
            searchConfrm: function (index) {
                if (index == 1) {
//                    console.log('搜索确认');
                    vm.searchToast = false;
                    var data = {
                        input: this.input
                    };
                    console.log(data);
                    $.authPost('/m/wish_histories/search_user', data, function (resp) {
                        if (!resp.error_code) {
                            vm.myWishList = true;
                            console.log(resp);
                            vm.show_wish_historiesresp.wish_histories;
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
    }

    vm = XVue(opts);
</script>
</body>
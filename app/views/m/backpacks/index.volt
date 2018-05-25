{{ block_begin('head') }}
{{ theme_css('/m/css/prize.css') }}
{{ theme_css('/m/css/apple.css') }}
{{ theme_js('/m/js/resize.js') }}
{{ theme_js('/m/js/jquery.min.js') }}
{{ theme_js('/js/vue.min.js') }}
{{ theme_js('/js/utils.js') }}
{{ block_end() }}


{% if !true %}

    <!-- 活动未开始 -->
    <img class="banner" src="/m/images/banner_prize.png" alt="">

    <div class="rules_box">
        <img  class="rules_bg" src="/m/images/rules_bg.png" alt="">
        <div class="title">
            <span class="dot_left"></span>
            <span class="title_text">活动规则</span>
            <span class="dot_right"></span>
        </div>
        <ul class="rules_list">
            <li>1. 房间内赠送钻石礼物达到一定数额，会出现小火箭图标。</li>
            <li>2. 随着钻石礼物增多，小火箭进度会增长，进度达到100%，开启领奖</li>
            <li>3. 领奖时限三分钟。</li>
            <li>4. 每晚0点活动重新开始。</li>
        </ul>
        <dl class="gift_type">
            <dt>礼物类型：</dt>
            <dd>金币，钻石，绝版礼物</dd>
        </dl>
    </div>
    <div class="footer">
        关注公众号Hi-6888, 充值有惊喜
    </div>

{% else %}

<img class="banner" src="/m/images/banner_prize.png" alt="">
<div id="app">
<!--滚动公告-->
<div class="notice_box">
    <div class="notice">
        <ul>

            <li v-for="item in history_list"><span>恭喜！用户${item.user}获得</span><span class="highlight">“${item.number}${item.name}”</span></li>

        </ul>
    </div>
</div>
<div class="gift_box">
    <div class="title">
        <span class="dot_left"></span>
        <span class="title_text">已抽中奖品</span>
        <span class="dot_right"></span>
    </div>
    <ul class="gift_list">

        <li v-for="item in target_list">
            <div class="list_img">
                <img :src="item.image_url" alt="">
            </div>
            <div class="list_txt">
                <span>${item.name}</span>
                <span>x${item.number}</span>
            </div>
        </li>

    </ul>
</div>

<div class="rules_box">
    <img  class="rules_bg" src="/m/images/rules_bg.png" alt="">
    <div class="title">
        <span class="dot_left"></span>
        <span class="title_text">活动规则</span>
        <span class="dot_right"></span>
    </div>

    <ul class="rules_list">
        <li>1. 房间内赠送钻石礼物达到一定数额，会出现小火箭图标。</li>
        <li>2. 随着钻石礼物增多，小火箭进度会增长，进度达到100%，开启领奖</li>
        <li>3. 领奖时限三分钟。</li>
        <li>4. 每晚0点活动重新开始。</li>
    </ul>
    <dl class="gift_type">
        <dt>礼物类型：</dt>
        <dd>金币，钻石，绝版礼物</dd>
    </dl>
</div>
<div class="footer">
    关注公众号Hi-6888, 充值有惊喜
</div>


<!--红包弹出层-->

<div class="cover">
    <div class="popup">
        <div class="popup_top">
            <div class="popup_head">
                <img  src="/m/images/popup_head.png" alt="">
            </div>
            <!--抽中金币或钻石奖品-->
            <div class="prize_box"  style="display:none;" >
                <span>恭喜您抽中</span>
                <div class="prize">
                    <img  class="ico" src="/m/images/ico.png" alt="">
                    <span> x999钻石 </span>
                </div>
            </div>
            <!--抽中多个奖品-->
            <div class="prize_box prizes_body" id="app">
                <span>恭喜您抽中</span>
                <div class="prize">

                    <div class="prizes_list" v-for="item in target_list">
                        <div class="prizes_img">
                            <img  class="ico" :src="item.image_url" alt="">
                        </div>
                        <span> x${item.number} </span>
                    </div>

                </div>

            </div>

        </div>
        <div class="popup_bottom">
            <a class="btn_popup" href="javascript:;" @click="createBackpack()">
                确定领取
            </a>

        </div>

    </div>
</div>
</div>

<script>

    $(function () {
        // 调用 公告滚动函数
        setInterval("noticeUp('.notice ul','-0.64rem',500)", 2000);

        // 弹出层
        var $btn= $('.btn');
        var $cover= $('.cover');

        //打开窗口
        $btn.on('click',function (e) {
            e.preventDefault();
            $cover.addClass('is-visible');
        });

        //关闭窗口
        $cover.on('click', function(e){
            /*点击确定按钮或者遮罩层关闭*/
            if( $(e.target).is('.btn_popup') || $(e.target).is('.cover') ) {
                e.preventDefault();
                $(this).removeClass('is-visible');
            }
        });

    });

    var opts = {
        data: {
            sid: "{{ sid }}",
            code: "{{ code }}",
            target_list: [],
            history_list: [],
            cache_list: [],
            get_boom: false
        },

        methods: {
            targetList: function () {

                $.authGet('/m/backpacks/prize', {
                    sid: vm.sid,
                    code: vm.code,
                }, function (resp) {
                    if (resp.error_code != undefined) {
                        $.each(resp.target, function (index, item) {
                            vm.target_list.push(item);
                            vm.cache_list[index] = {'id':item.id, 'number':item.number};
                        })
                        if (resp.error_code == 0) $('.cover').addClass('is-visible');
                    }
                    return ;

                })

            },
            
            boomHistories: function () {
                $.authGet('/m/backpacks/history', {
                    sid: vm.sid,
                    code: vm.code,
                }, function (resp) {
                    $.each(resp.boom_histories, function (index, item) {
                        vm.history_list.push(item);
                    })
                })
            },
            
            createBackpack: function () {

                if (vm.get_boom) {
                    console.log('领取过!');
                    return ;
                }
                $.authPost('/m/backpacks/create', {
                    sid: vm.sid,
                    code: vm.code,
                    target: vm.cache_list
                }, function (resp) {
                    console.log(resp);
                    vm.get_boom = true;
                })
            }

        }
    };
    vm = XVue(opts);
    vm.targetList();
    vm.boomHistories();

    // 公告滚动 封装函数
    function noticeUp(obj,top,time) {
        $(obj).animate({
            marginTop: top
        }, time, function () {
            $(this).css({marginTop: "0"}).find(":first").appendTo(this);
        })
    }
</script>
{% endif %}
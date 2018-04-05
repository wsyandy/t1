{{ block_begin('head') }}
{{ theme_css('/m/css/lucky_draw_activity.css') }}
{{ theme_js('/m/js/jquery.rotate.min.js') }}
{{ block_end() }}

<div id="app">
    <!-- 获得红包弹框开始 -->
    <div class="hongbao">
        <img src="/m/images/close_btn.png" class="close_btn">
        <div class="title">恭喜您获得</div>
        <div class="hb_money"><span class="hb_zhong"></span><img src="/m/images/jinbi.png"></div>
    </div>
    <div class="hongbao_bg"></div>
    <!-- 获得红包弹框结束 -->
    <a href="/m/activity_histories?sid={{ sid }}&code={{ code }}&activity_id={{ activity_id }}"><img src="/m/images/jilu.png" class="jilu"></a>
    <div class="zhuanpan_wrap">
        <div class="banner_bg">
            <img src="/m/images/banner.png" alt="" class="banner">
            {#<div class="zhuanpan_txt">#}
                {#<div class="zp_name">#}
                    {#恭喜<span>晓晓</span>获得了<span>10000金币</span>#}
                {#</div>#}
            {#</div>#}
        </div>
        <!-- 转盘开始 -->
        <div class="g-lottery-case">
            <div class="g-left">
                <div class="g-lottery-box">
                    <div class="g-lottery-img" @click="luckyDraw()">
                        <a class="playbtn" href="javascript:;" title="开始抽奖"></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- 转盘结束 -->
    </div>
    <div class="jihui">共有<b>${lucky_draw_num}</b>次抽奖机会</div>
    <div class="guize">
        <h3>活动规则</h3>
        <div class="guize_text">
            <ul>
                <li>
                    <p>1、</p>
                    <p>每赠送一个1314钻石愿灯，获得1次抽奖机会；</p>
                </li>
                <li>
                    <p class="color_bg">0、</p>
                    <p>每赠送一个3344钻石生日party，获得3次抽奖机会；</p>
                </li>
                <li>
                    <p class="color_bg">0、</p>
                    <p>每赠送一个9999钻石梦幻城堡，获得10次抽奖机会；</p>
                </li>
                <li>
                    <p class="color_bg">0、</p>
                    <p>每充值998元，获得3次抽奖机会；</p>
                </li>
                <li>
                    <p class="color_bg">0、</p>
                    <p>每充值2888元，获得10次抽奖机会；</p>
                </li>
                <li>
                    <p class="color_bg">0、</p>
                    <p>每充值5888元，获得22次抽奖机会；</p>
                </li>
                <li style="margin-top:5px;">
                    <p>2、</p>
                    <p>抽中五位号和六位号的用户请联系客服（ID：100101）获取号码，其他抽中的礼物将会直接放入您的账户。</p>
                </li>
            </ul>
        </div>
    </div>
    <div class="guize_bottom">本活动最终解释权归Hi语音官方团队</div>
</div>

<script>
    $(function () {
        $(".hongbao").hide();
        $(".hongbao_bg").hide();
    });

    function start(random) {
        switch (random) {
            case 1:
                rotateFunc(1, 0, '10000');
                break;
            case 2:
                rotateFunc(2, 45, '5位数幸运号');
                break;
            case 3:
                rotateFunc(3, 90, '1000');
                break;
            case 4:
                rotateFunc(4, 135, '6位数幸运号!');
                break;
            case 5:
                rotateFunc(5, 180, '100');
                break;
            case 6:
                rotateFunc(6, 225, '小马驹座驾!');
                break;
            case 7:
                rotateFunc(7, 270, '神秘礼物!');
                break;
            case 8:
                rotateFunc(8, 315, '兰博基尼座驾!');
                break;
        }
    }

    function rotateFunc(awards, angle, text) {
        $btn = $(".playbtn")
        vm.lucky_draw = true;
        $btn.stopRotate();
        $btn.rotate({
            angle: 0,
            duration: 6000, //旋转时间
            animateTo: angle + 4000, //让它根据得出来的结果加上1140度旋转
            callback: function () {
                vm.lucky_draw = false; // 标志为 执行完毕
                // alert(text);
                $(".hb_zhong").html(text);

                function colse_fd() {
                    $(".hongbao").hide();
                    $(".hongbao_bg").hide();
                };
                var doc_height = $(document).height();
                var w_height = $(window).height();
                var w_width = $(window).width();

                $(".hongbao").show();
                $(".hongbao_bg").show();

                $(".hongbao_bg").attr("style", "height:" + doc_height + "px");
                var div_width = $(".hongbao").width();
                var div_height = $(".hongbao").height();

                var div_left = w_width / 2 - div_width / 2 + "px";
                var div_top = w_height / 2 - div_height / 2 + "px";

                $(".hongbao").css({
                    "left": div_left,
                    "top": div_top
                });

                $(".close_btn").click(function () {
                    colse_fd();
                });
            }
        });
    };

    var opts = {
        data: {
            lucky_draw_num: '{{ lucky_draw_num }}',
            lucky_draw: false,
            sid: '{{ sid }}',
            code: '{{ code }}',
            activity_id: '{{ activity_id }}'

        },
        methods: {
            luckyDraw: function () {

                if (vm.lucky_draw) {
                    return
                } // 如果在执行就退出

                vm.lucky_draw = true; // 标志为 在执行

                $.authPost('/m/activities/lucky_draw', {
                    sid: vm.sid,
                    code: vm.code,
                    activity_id: vm.activity_id
                }, function (resp) {
                    vm.lucky_draw = false;
                    if (0 == resp.error_code) {
                        vm.lucky_draw_num = resp.lucky_draw_num;
                        start(resp.type);
                        return;
                    }

                    alert(resp.error_reason);
                });
            }
        }
    };

    vm = XVue(opts);
</script>

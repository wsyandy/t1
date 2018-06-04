{{ block_begin('head') }}
{{ theme_css('/m/css/turntable_draw_histories.css') }}
{{ theme_js('/m/js/resize.js', '/m/js/turntable_draw_histories.js', '/m/js/jquery.rotate.min.js') }}
{{ block_end() }}

<div class="zp_top" id="app">
    <div class="huode_bg">您刚刚获得了xxx</div>
    <div class="beibao_bg">背包</div>
    <!-- 转盘开始 -->
    <!--         <div class="g-lottery-case">
                <div class="g-left">
                    <div class="g-lottery-box">
                        <div class="g-lottery-img">
                            <a class="playbtn" href="javascript:;" title="开始抽奖"></a>
                        </div>
                    </div>
                </div>
            </div> -->
    <!-- 转盘结束 -->
    <div class="turntable-box">
        <div class="turntable-bg zp_bg">

            <div class="pointer">
                <img src="/m/images/turntable_draw_histories_playbtn.png" alt="pointer">

            </div>
            <div class="rotate">
                <img id="rotate" src="/m/images/turntable_draw_histories_bg_lottery.png" alt="turntable">

            </div>

            <div class="gold_box">
                <!-- 撒金币-->
                <div class="clipped-box"></div>
            </div>

        </div>
    </div>
</div>
<div class="zp_choujiang">
    <ul class="multiple">
        <li class="multiple_1 on"></li>
        <li class="multiple_10"></li>
    </ul>
    <div class="choujiang_btn">
        <!--<img src="/m/images/btn_up.png">-->
    </div>
    <div class="zuan_num"><i></i><span>X100</span></div>
    <h3>您还剩余XX次1倍抽奖赠送机会</h3>
    <div class="finger">
    </div>

</div>
<div class="gongxi">
    <div id="demo" class="box">
        <div id="demo1" class="boxIn">
            <ul>
                <li>恭喜 <span>是减肥是浪费</span> 获得了 <span>钻石*10000</span></li>
                <li>恭喜 <span>是减肥是浪费是减</span> 获得了 <span>钻石*10000</span></li>
                <li>恭喜 <span>是减肥是浪费</span> 获得了 <span>钻石*10000</span></li>
                <li>恭喜 <span>是减肥是浪费</span> 获得了 <span>钻石*10000</span></li>
                <li>恭喜 <span>是减肥是浪费</span> 获得了 <span>钻石*10000</span></li>
                <li>恭喜 <span>是减肥是浪费</span> 获得了 <span>钻石*10000</span></li>
                <li>恭喜 <span>是减肥是浪费</span> 获得了 <span>钻石*10000</span></li>
                <li>恭喜 <span>是减肥是浪费</span> 获得了 <span>钻石*10000</span></li>
                <li>恭喜 <span>是减肥是浪费</span> 获得了 <span>钻石*10000</span></li>
            </ul>
        </div>
        <div id="demo2"></div>
    </div>
</div>
<div class="jiangpin_title">奖品</div>
<div class="jiangpin_text">
    <p>10-1000钻石：10钻、30钻、100钻、500钻、1000钻、10000钻、100000钻。</p>
    <p>金币：50金币、100金币。</p>
    <p>幸运礼物：随版本更新</p>
    <p>奢华坐骑：梦境奇迹、UFO、光电游侠（有使用期限）</p>
    <p>幸运靓号：（需要联系客服xxxxxx）</p>
</div>
<div class="guize_title">活动规则</div>
<div class="guize_text">
    <p>1、10钻获得一次转盘机会，100钻选择10倍能增加中大奖几率。</p>
    <p>2、抽中的钻石、金币、座驾和幸运礼物将会直接放入我的背包，幸运靓号需用户联系客服。</p>
    <p>3、每日分享好友或群组可获得一次转盘机会、仅限一次，次日更新。第二次分享无法获得抽奖机会。</p>
</div>
<div class="zp_bottom"><p>活动最终解释权归HI语音官方团队</p></div>


<!-- 钻石不足弹框 -->
<div class="mask">
    <div class="popup_tips">
        <h1>提示</h1>
        <p>您的钻石余额不足，请先充值每日分享抽奖仅有一次机会，0点更新！</p>
        <div class="btn_list">
            <a href="#" class="btn_icon" id="noTips">
                <img src="/m/images/btn_tips.png" alt="">
            </a>
            <a href="#" class="btn_icon" id="tips10">
                <img src="/m/images/btn_tips10.png" alt="">
            </a>
        </div>
        <img class="close" src="/m/images/close.png" alt="">
    </div>

    <div class="popup_box">
        <h1>提示</h1>
        <p>您的钻石余额不足，请先充值每日分享抽奖仅有一次机会，0点更新！</p>
        <div class="btn_list">
            <a href="#" class="btn_icon">
                <img src="/m/images/btn_recharge.png" alt="">
            </a>
            <a href="#" class="btn_icon">
                <img src="/m/images/btn_share.png" alt="">
            </a>
        </div>
        <img class="close" src="/m/images/close.png" alt="">
    </div>
</div>

<script>
    /*转盘抽奖*/
    $(function () {
        /*选择倍数*/

        $('.multiple li').click(function (e) {

            $(this).addClass('on').siblings().removeClass('on')

        });
        $('#tips10').click(function (e) {
            $('.multiple li').last().addClass('on').siblings().removeClass('on');
            $('.mask').fadeOut();
            $('.popup_tips').fadeOut();
        });


        var rotateTimeOut = function () {
            $('#rotate').rotate({
                angle: 0,
                animateTo: 2160,
                duration: 8000,
                callback: function () {
                    alert('网络超时，请检查您的网络设置！');
                }
            });
        };
        var bRotate = false;
        var diamonds = 1;
        /*判断钻石够不够抽奖 0 为不够 弹窗提示框，1 为够*/

        var rotateFn = function (awards, angles) {
            bRotate = !bRotate;
            $('#rotate').stopRotate();
            $('#rotate').rotate({
                angle: 0,
                animateTo: angles + 3622.5,
                duration: 500,
                callback: function () {
                    bRotate = !bRotate;
//                        console.log(awards);

                    switch (awards) {
                        case 0:
                            console.log(0, '幸运靓号');
                            break;
                        case 1:
                            console.log(1, '神秘礼物');
                            break;
                        case 2:
                            console.log(2, '奢华座驾');
                            break;
                        case 3:
                            console.log(3, '100000钻');
                            vm.showGold(40, 1);
                            break;
                        case 4:
                            console.log(4, '10000钻');
                            vm.showGold(30, 1);
                            break;
                        case 5:
                            console.log(5, '金币');
                            vm.showGold(30, 0);
                            break;
                        case 6:
                            console.log(6, '幸运礼物');
                            break;
                        case 7:
                            console.log(7, '10-10000钻');
                            vm.showGold(20, 1);
                            break;
                    }

                }
            })
        };

        $('.choujiang_btn').click(function () {
            if (bRotate) return;
            if (diamonds) {
                var i = rnd(0, 7);
                var angle = [337.5, 22.5, 67.5, 112.5, 157.5, 202.5, 247.5, 292.5];
                rotateFn(i, angle[i]);
            } else {
                $('.mask').fadeIn();
                $('.popup_box').fadeIn();
            }

        });


        $('#noTips').click(function () {
            $('.mask').fadeOut();
            $('.popup_tips').fadeOut();
        });
        $('.close').click(function () {
            $('.mask').fadeOut();
            $('.popup_box').fadeOut();
        });
        $.fn.scrollFont();
    });

    function rnd(n, m) {
        return Math.floor(Math.random() * (m - n + 1) + n)
    }

    (function ($) {
        /*文字滚动*/
        $.fn.scrollFont = function (o) {
            var d = {
                scrollWap: '#demo',
                scrollWapIn: '#demo1',
                cloneCon: '#demo2',
                speed: 180
            };
            var o = $.extend(d, o);
            var demo = $(d.scrollWap).scrollTop();
            var ul = $(d.scrollWapIn).height();
            var one = $(d.scrollWapIn).html();
            var demo2 = $(d.cloneCon).append(one);
            var inter = setInterval(fn, d.speed);

            function fn() {
                if (demo >= 0) {
                    $(d.scrollWap).scrollTop(demo++)
                }
                if (demo == ul) {
                    demo = 0;
                }
            }

            $(d.scrollWap).mouseover(function () {
                clearInterval(inter);
            });
            $(d.scrollWap).mouseout(function () {
                inter = setInterval(fn, d.speed);
            });
        }
    })(jQuery);

</script>



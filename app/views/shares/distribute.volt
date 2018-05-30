<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>邀请--注册</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/shares/css/distribute_apple.css">
    <!--音频播放器-->
    <link rel="stylesheet" href="/shares/css/distribute_register.css">
</head>
<body>
<div id="app">
    <div class="register_head">
        <img class="cdControllerArm" src="/shares/images/cdControllerArm.png" alt="">
        <div class="audio_box">
            <!--音乐控制面板-->
            <div class="audio_btn">
                <!--旋转碟片-->
                <div class="audio_cover" id="play">
                    <!--<div class="cdCover"></div>-->
                </div>
                <!--暂停/播放按钮-->
                <div class="btn_play btn_pause"></div>
            </div>
            <audio id="music" autoplay src="/shares/distribute_register.mp3">
                Your browser does not support HTML5 audio.
            </audio>
        </div>
    </div>
    <div class="register_body">
        <h3>Hi语音</h3>
        <div class="register_title"><span>备受年轻人欢迎的语音直播平台</span></div>

        <div class="register_box">
            <ul>
                <li>
                    <input class="input_phone" type="text" placeholder="请输入手机号" value="" name="mobile"
                           v-model="mobile">
                </li>
                <li>
                    <input type="text" placeholder="图形验证码" maxlength="4" class="input_verify" v-model="captcha_code">
                    <img class="image_token" data-cont="/" id="captcha" @click="getCaptcha()">
                    <input type="hidden" class="get_verify" id="image_token" value="" v-model="image_token"/></input>
                </li>
                <li>
                    <input class="input_verify" type="text" placeholder="请输入验证码" disabled name="auth_code"
                           v-model="auth_code">
                    <input type="button" class="get_verify" value="获取验证码" @click="getAuthCode()"/>
                </li>
                <li>
                    <input class="input_password" type="password" placeholder="请输入密码" value="" v-model="password">
                </li>
            </ul>
            <div class="register_btn " @click="register">
                立即注册
            </div>
        </div>
    </div>

    <div class="pup_cover" id="pup_tips">
        <div class="pup_code">${error_text}</div>
    </div>
    <!--底部导航-->
</div>

<script src="/shares/js/jquery.min.js"></script>
<script src="/js/utils.js"></script>
<!--旋转动画插件-->
<script src="/shares/js/jquery.rotate.min.js"></script>
<!--音频播放器-->
{#<script src="/shares/js/audio_player.js"></script>#}

<script src="/js/vue/2.0.5/vue.min.js"></script>
<!--倒计时-->
<script>
    var opts = {
        data: {
            mobile: '',
            auth_code: '',
            password: '',
            share_history_id: '{{ share_history_id }}',
            sms_token: '',
            register_status: false,
            send_status: false,
            code: "{{ code }}",
            image_token: '',
            captcha_code: '',
            error_text: ''

        },
        methods: {
            register: function () {
                if (vm.register_status) {
                    return;
                }

                if (!checkCaptchaParams()) {
                    return;
                }

                if (!vm.mobile) {
                    $tips.show(10).delay(1000).fadeOut();
                    vm.error_text = "请输入正确的手机号";
                }

                if (!vm.auth_code) {
                    $tips.show(10).delay(1000).fadeOut();
                    vm.error_text = "请输入正确的短信验证码";
                    return;
                }

                vm.register_status = true;

                var data = {
                    mobile: vm.mobile,
                    auth_code: vm.auth_code,
                    password: vm.password,
                    sms_token: vm.sms_token,
                    image_token: vm.image_token,
                    captcha_code: vm.captcha_code,
                    auth_type: 'register',
                    share_history_id: vm.share_history_id,
                    code: vm.code
                };
                $.authPost('/shares/mobile_auth', data, function (resp) {
                    $tips.show(10).delay(1000).fadeOut();
                    vm.error_text = resp.error_reason;
                    if (resp.error_code == 0) {
                        window.location = resp.down_url;
                    } else {
                        vm.register_status = false;
                    }
                })
            },

            getAuthCode: function (event) {
                if (vm.send_status) {
                    return;
                }

                vm.send_status = true;
                var data = {
                    mobile: vm.mobile,
                    image_token: vm.image_token,
                    captcha_code: vm.captcha_code,
                    auth_type: 'register',
                    share_history_id: vm.share_history_id,
                    code: vm.code
                };

                $.authPost('/shares/mobile_auth', data, function (resp) {
                    if (resp.error_code != 0) {
                        $tips.show(10).delay(1000).fadeOut();
                        vm.error_text = resp.error_reason;
                        vm.send_status = false;
                    } else {
                        vm.sms_token = resp.sms_token;
                        $tips.show(10).delay(1000).fadeOut();
                        vm.error_text = resp.error_reason;
                    }

                })
            },
            getCaptcha: function () {
                getCaptchaImage();
            }
        }
    };
    var vm = XVue(opts);
    $(function () {
        reurl();
        nplay();
        iplay();
        var $tel = $(".input_phone");
        var $verify = $(".input_verify");
        $tips = $("#pup_tips");
        var $getVerify = $(".get_verify");
        var countdown = 60;
        var timer;

        $getVerify.on('click', function () {
            var mobileNum = $tel.val();
            var isphone = isMobile(mobileNum);
            setTimeout(function () {
                settime(isphone)
            }, 1000);

        })

        function settime(isphone) {
            clearTimeout(timer);
            if (isphone && vm.send_status) {
                if (countdown === 0) {
                    $getVerify.attr("disabled", false).val("获取验证码");
                    $verify.attr("disabled", true);
                    countdown = 60;
                    return;
                } else {
                    $getVerify.addClass('red');
                    $verify.attr("disabled", false);
                    $getVerify.attr("disabled", true).val(countdown + "s后重发");
                    countdown--;
                }
                timer = setTimeout(function () {
                    settime(isphone)
                }, 1000);
                $('.register_btn').addClass('btn_submit')
            } else {
                $tel.focus().empty()
            }
        }


        /*验证手机号*/
        function isMobile(value) {
            var validateReg = /^((\+?86)|(\(\+86\)))?1[0-9]{10}$/;
            return validateReg.test(value);
        }

    })

    function getCaptchaImage() {
        $.get('/captcha', function (resp) {
            var captcha = document.getElementById('captcha');
            captcha.src = resp.image_data;
            vm.image_token = resp.image_token;
        });
    }

    function reurl() {
        url = location.href; //把当前页面的地址赋给变量 url
        var times = url.split("&"); //分切变量 url 分隔符号为 "?"
        var length = times.length;
        if (times[length-1] != 'tt=1') { //如果?后的值不等于1表示没有刷新
            url += "&tt=1"; //把变量 url 的值加入 ?1
            self.location.replace(url); //刷新页面
        }
    }

    getCaptchaImage();

    function checkCaptchaParams() {
        if (!vm.image_token || !vm.captcha_code) {
            $tips.show(10).delay(1000).fadeOut();
            vm.error_text = "请输入正确的验证码";
            return false;
        }

        return true;
    }

    var rotatetimer, /* 旋转定时器 */
        isPlay = true, /* 播放状态 */
        angle = 0, /* 旋转角度 */
        $cdControllerArm = $('.cdControllerArm'),
        $cover = $('.audio_cover'),
        $btnPlay = $('.btn_play'),
        $music = $('#music'),
        music = $music.get(0);
    /* jQuery对象 转换为 DOM对象 以便于操作 Audio 对象*/


    /*播放*/
    $btnPlay.on('click', function () {
        isPlay ? nplay() : iplay();
    });

    /*播放状态*/
    function iplay() {
        clearInterval(rotatetimer);
        $btnPlay.removeClass('btn_pause');
        music.play();

        isPlay = true;
        $cdControllerArm.addClass("cd_play");
        /* jquery.rotate 旋转动画插件  */
        rotatetimer = setInterval(function () {
            angle += 1;
            $cover.rotate(angle);
        }, 20);
    }

    /*暂停状态*/
    function nplay() {
        clearInterval(rotatetimer);
        /* 清除选择动画 */
        music.pause();
        isPlay = false;
        $btnPlay.addClass('btn_pause');
        /* 添加暂停按钮 */
        $cdControllerArm.removeClass("cd_play");
    }
</script>
</body>
</html>
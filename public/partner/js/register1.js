/*注册协议*/
var $agree = $('#agreeScroll'), $Btn = $('.agree-btn'), $agreeShow = $('.agree-show'), $regShow = $('.reg-show'),
    $warn = $(".warn");

$agree.scroll(function () {
    var sTop = $(this).scrollTop();
    var cH = $(this).innerHeight();
    var sH = $(this).prop('scrollHeight');
    if (sTop + cH >= sH) {
        $Btn.addClass("active")
    } else {
        $Btn.removeClass("active")
    }
    $agree.removeClass("warn");
    $warn.fadeOut()
});

$Btn.on("click", function () {
    if ($(this).hasClass("active")) {
        $agree.removeClass("warn");
        $agreeShow.fadeOut();
        tabs('.swiperTab > li', '.swiper-container', 'cur', 0);
    } else {
        $agree.addClass("warn");
        $warn.fadeIn();

    }

});


// 验证码倒计时
var validCode = true;
$(".get_auth_code").click(function () {
    var time = 10;
    var $code = $(this);
    $code.html(time + "s后获取");
    if (validCode) {
        validCode = false;
        var codeTimer = setInterval(function () {
            time--;
            $code.html(time + "s后获取");
            if (time == 0) {
                clearInterval(codeTimer);
                $code.html("重新获取");
                validCode = true;
            }
        }, 1000)
    }
})
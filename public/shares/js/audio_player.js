
/**
 *
 * 作者 月影 (QQ:253737688)
 * json 数据原创模拟 素材来自网络
 * 音乐外链生成 http://www.170mv.com/tool/song/
 * 酷狗        http://www.kuwo.cn/
 * LRC歌词     http://lrc.bzmtv.com/
 *
 */
(function($){
    var rotatetimer,    /* 旋转定时器 */
        isPlay = true, /* 播放状态 */
        angle = 0,      /* 旋转角度 */
        $cdControllerArm = $('.cdControllerArm'),
        $cover = $('.audio_cover'),
        $btnPlay= $('.btn_play'),
        $music=$('#music'),
        music = $music.get(0);          /* jQuery对象 转换为 DOM对象 以便于操作 Audio 对象*/


    /*播放*/
    $btnPlay.on('click', function() {
        isPlay ? nplay() : iplay() ;
    });
    /*播放状态*/
    function iplay() {
        clearInterval(rotatetimer);
        $btnPlay.removeClass('btn_pause');
        music.play();

        isPlay = true;
        $cdControllerArm.addClass("cd_play");
        /* jquery.rotate 旋转动画插件  */
        rotatetimer = setInterval(function() {
            angle += 1;
            $cover.rotate(angle);
        }, 20);
    }
    /*暂停状态*/
    function nplay() {
        clearInterval(rotatetimer);     /* 清除选择动画 */
        music.pause();
        isPlay = false;
        $btnPlay.addClass('btn_pause'); /* 添加暂停按钮 */
        $cdControllerArm.removeClass("cd_play");
    }
    iplay();



})(jQuery);
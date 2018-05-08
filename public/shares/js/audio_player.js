
/**
 *
 * 作者 月影 (QQ:253737688)
 * json 数据原创模拟 素材来自网络
 * 音乐外链生成 http://www.170mv.com/tool/song/
 * 酷狗        http://www.kuwo.cn/
 * LRC歌词     http://lrc.bzmtv.com/
 *
 */
$(function () {
    var rotatetimer,    /* 旋转定时器 */
        isNext = false,  /* 播放结束是下一首还是暂停 */
        isPlay = false, /* 播放状态 */
        angle = 0,      /* 旋转角度 */
        i = 0,
        max = 0,        /* 时长 */
        value = 0,
        $cdControllerArm = $('.cdControllerArm'),
        $cover = $('.audio_cover'),
        $cdCover = $('.cdCover'),
        $btnPlay= $('.btn_play'),
        musList,
        $timeCur = $('.time_cur'),
        $timeLong = $('.time_long'),
        $music=$('#music'),
        music = $music.get(0);          /* jQuery对象 转换为 DOM对象 以便于操作 Audio 对象*/

    (function getData() {
        $.ajax({
            url: "/shares/js/music.json",
            cache:false,
            dataType: "json",
            success:function(data){
                musList = data;         /* 获取json数据 赋值 给musList数组 */
                lens=musList.length;
                /*初始化*/
                $cdCover.css('background-image', "url(" + musList[0].cov + ")");/*歌曲海报 */
                $music.attr('src', musList[0].voi);          /* 歌曲链接 */
                getTime();                                   /* 初始化第一个歌曲时长 */
                iplay()                                       /* 打开自动播放 */
            },
            error:function(){
                console.log((" ajax异步获取歌曲， 必须服务器支持才能打开，例如：wampserver ，MAMP 或者 webstorm 浏览器预览"));
            }
        });

    })();

    /* 获取歌曲时长 因为要加载完成才能获取时长，所以设置 延时获取*/
    function getTime() {
        setTimeout(function () {
            isNaN(music.duration)?getTime():$timeLong.html(toTwo(music.duration))
        });
    }

    /*播放*/
    $btnPlay.on('click', function() {
        console.log(11111111);
        isPlay ? nplay() : iplay() ;
    });



    /*播放歌曲方法*/
    function play(j) {
        $cdCover.css('background-image', "url(" + musList[j].cov + ")");  /* 更换对应歌曲海报 */
        $music.attr('src', musList[j].voi);                             /* 更换对应歌曲链接 */

        $singer.html(musList[j].inf);                                   /* 歌手信息，在此直接放字符串了，也可以像歌词一样单独列出来 */
        $songList.find('.cur').removeClass('cur');
        $songList.find('li').eq(i).addClass('cur');                     /* 当前播放歌曲高亮 */
        isPlay ? iplay(): nplay();
        renderLyric(j);                                                  /* 获取对应歌词 */
        getTime();                                                       /* 获取对应时长 */
    }

    /*播放状态*/
    function iplay() {
        clearInterval(rotatetimer);
        $btnPlay.removeClass('btn_pause');
        (music.onloadeddata = function () {         /* loadeddata 当浏览器已加载音频/视频的当前帧时触发。*/
            max = Math.round(music.duration);
            $timeLong.html(toTwo(music.duration));  /*加载载歌曲时长*/
        })();
        music.play();
        isPlay = true;
        $cdControllerArm.addClass("cd_play");
        /* jquery.rotate 旋转动画插件  */
        rotatetimer = setInterval(function() {
            angle += 1;
            $cover.rotate(angle);
        }, 20);
    }

    /*  歌曲当前播放时间  */
    music.ontimeupdate = function () {
        value = Math.round(music.currentTime);
        $timeCur.html(toTwo(value));/*加载载歌曲当前播放时间*/
        music.onended = function () {
            console.log('音频播放完成');
            isNext?next():nplay();    /* 判断歌曲播放结束 是否下一首,*/
        }
    };


    /*暂停状态*/
    function nplay() {
        music.pause();
        isPlay = false;

        clearInterval(rotatetimer);     /* 清除选择动画 */
        $btnPlay.addClass('btn_pause'); /* 添加暂停按钮 */
        $cdControllerArm.removeClass("cd_play");
    }
    /*时间格式转换器 - 00:00*/
    function toTwo(num){
        function changInt(num){
            return (num < 10) ? '0'+num : num;
        }
        return changInt(parseInt(num/60))+":"+ changInt(Math.round(num%60));
    }

})
// ;(function($){
//
// })(jQuery);
{{ block_begin('head') }}
{{ weixin_css('course_details.css','sale.css') }}
{{ block_end() }}
<div class="main_content">
    <div class="course_message">
        <div class="course_banner">
            <img src="/wx/layuyin/images/chuanyi.jpg" alt="">
            <!-- 2.0修改 -->
            <span class="tip"></span>
            <span class="share">分享赚</span>
        </div>
        <div class="course_message_box">
            <p><span class="xilie">系列</span>迅速提升吸引力的就打穿衣术，哪几种？</p>
            <div class="course_money_num">
                <b>¥99.00</b>
                <span>
            <img src="/wx/layuyin/images/star_h_icon.png">
            <img src="/wx/layuyin/images/star_h_icon.png">
            <img src="/wx/layuyin/images/star_h_icon.png">
            <img src="/wx/layuyin/images/star_h_icon.png">
            <img src="/wx/layuyin/images/star_icon.png">
            4.9分
          </span>
            </div>
            <div class="course_message_text">
                <!-- <i>2017-12-11 12:00</i> -->
                <div class="course_message_text_box">
                    <p>已更新 <span>9</span>  期&nbsp;|&nbsp;</p>
                    <p>预计更新 <span>9</span>  期</p>
                </div>
                <p>1200人学习</p>
            </div>
        </div>
    </div>
    <div class="course_teacher">
        <div class="course_teacher_header">
            <img src="/wx/layuyin/images/home_classify_course.png" alt="header">
        </div>
        <div class="course_teacher_introduce line_hide">
            <div class="course_teacher_guanzhu">
                <p class="line_hide">周华健</p>
                <b class="course_details_gz" onclick="gzBtn(this)"><i></i> <a href="JavaScript:;">关注</a></b>
            </div>
            <span class="cur line_hide">自我介绍 : 北方的艳阳纷飞大雪纷飞飞…北方的北方的艳阳纷飞大雪纷飞飞…北方的北方的艳阳纷飞大雪纷飞飞</span>
        </div>
    </div>
    <ul class="course_introduce">
        <li>
            <div class="course_introduce_title">
                <span>课程介绍</span>
            </div>
            <!--  <div class="course_introduce_play">
               <img src="" alt="">
               <span class="play_but"></span>
             </div> -->
        </li>
        <li>
            <div class="course_introduce_title">
                <span class="wire"></span>
                <span>课程介绍</span>
            </div>
            <p class="course_introduce_text">艾学教育, 价值388元中学学科目辅导免费试听课程赶紧艾学教育, 价值388元中学学科目辅导免费。</p>
        </li>
        <li>
            <div class="course_introduce_title">
                <span class="wire"></span>
                <span>课程适合人群</span>
            </div>
            <p class="course_introduce_text">20-50岁。</p>
        </li>
        <li>
            <div class="course_introduce_title">
                <span class="wire"></span>
                <span>课程大纲</span>
            </div>
            <p class="course_introduce_text">还不错</p>

        </li>
        <li class="course_text_bottom">
            <div class="course_introduce_title">
                <span class="wire"></span>
                <span>听课须知</span>
            </div>
            <p class="course_introduce_text">1、本次课程是付费课，购买后即可进入课堂</p>
            <p class="course_introduce_text">2、上课形式为ppt与语音交互模式</p>
            <p class="course_introduce_text">3、本次课程内容永久保存，可反复收听</p>
        </li>
    </ul>
    <div class="course_buy_wrap">
        <div class="course_buy_box course_details_single">
            <div class="course_collect" onclick="scBtn(this)">
                <i></i>
                <p>收藏</p>
            </div>
            <div class="course_buy_list">
                <ul>
                    <li>
                        <a href="/wx/payments/weixin"><span>购买课程</span></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        // 自我介绍显示隐藏
        $('.course_teacher_introduce span').click(function(){
            $(this).toggleClass('cur line_hide');
            $('.course_teacher_introduce').toggleClass('text_auto line_hide');
        })
    })
    // 关注
    function gzBtn(val){
        var obj=$(val);
        if(obj.hasClass('gz_cur_btn')){
            obj.removeClass('gz_cur_btn');
            obj.children('a').text('关注');
        }else{
            obj.addClass('gz_cur_btn');
            obj.children('a').text('已关注');
        }
    }

    // 收藏
    function scBtn(val){
        var objsc=$(val);
        if(objsc.hasClass('course_collect_cur')){
            objsc.removeClass('course_collect_cur');
            objsc.children('p').text('收藏');
        }else{
            objsc.addClass('course_collect_cur');
            objsc.children('p').text('已收藏');
        }
    }
</script>
</body>
</html>

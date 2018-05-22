{{ block_begin('head') }}
{{ weixin_css('mine_course_ask.css') }}
{{ weixin_js('index.js') }}
{{ block_end() }}

<div class="main_content">
    <div class="haeder_nav">
        <span class="haeder_left_back"></span>
        <span>我的提问</span>
        <span class="haeder_right_but"></span>
    </div>
    <div class="pub_collect_title">
        <ul>
            <li class="pub_collect_title_cur">课程</li>
            <li>问答</li>
        </ul>
    </div>
    <div class="mine_course_ask_list">
        <h3>这是标题</h3>
        <p class="row_hide">为什么这节课感觉好难，老师可以讲的通熟易懂些吗，我
            的脑回路有点转不过来脑回路有点转不过来脑回路有点...</p>
        <div class="mine_course_ask_time">
            <span>2018/03/10</span>
            <b>已回复</b>
        </div>
    </div>
    <div class="mine_course_ask_list">
        <h3>这是标题</h3>
        <p class="row_hide">为什么这节课感觉好难，老师可以讲的通熟易懂些吗，我
            的脑回路有点转不过来脑回路有点转不过来脑回路有点...</p>
        <div class="mine_course_ask_time">
            <span>2018/03/10</span>
            <b>已回复</b>
        </div>
    </div>
    <div class="pub_end">
        <h3>我是有底线的</h3>
        <p></p>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        $('.pub_collect_title ul li').each(function(){
            $(this).click(function(){
                $(this).addClass('pub_collect_title_cur').siblings().removeClass('pub_collect_title_cur');
            })
        })

    })
</script>
{{ block_begin('head') }}
{{ weixin_css('mine.css') }}
{{ weixin_js('index.js') }}
{{ block_end() }}

<div class="main_content">
    <div class="haeder_nav">
        <span class="haeder_left_but"></span>
        <span>我的</span>
        <span class="haeder_right_but"></span>
    </div>

    <div class="mine_message">
        <div class="header_img">
            <img class="header_img" src="/wx/layuyin/images/default_header.png" alt="">
        </div>
        <div class="mine_message_text row_hide">
            <p>东溪青竹</p>
            <span class="row_hide">自我介绍 : 缤纷的世界里，默数时光的灿烂</span>
        </div>
    </div>
    <ul class="mine_list">
        <li><span class="create"></span>创建课程/我的课程</li>
        <a href="/wx/micro_class/buy_course"><li><span class="purchase"></span> 购买记录<span class="wire"></span></li></a>
        <li> <span class="course"></span> 听课记录<span class="wire"></span></li>
        <a href="/wx/micro_class/mine_course"><li><span class="quiz"></span> 我的提问<span class="wire"></span></li></a>
        {#<li><span class="mine_gz"></span> 我的关注</li>#}
        {#<li class="mine_mar"><span class="mine_share"></span> 分销中心<span class="wire"></span></li>#}
        {#<li><span class="mine_money"></span> 我的钱包</li>#}
        <li><span class="set"></span> 设置</li>
    </ul>


    <div class="footer_nav">
        <div class="footer_nav_box">
            <a class="home"href="/wx/micro_class">
                <p>首页</p>
            </a>
            <a>
                <div class="play_but">
                    <div class="play_but_box">
                        <img src="/wx/layuyin/images/home_classify_discount.png" alt="play_img">
                        <span class="but"></span>
                    </div>
                </div>
            </a>
            <a class="mine cur">
                <p>我的</p>
            </a>
        </div>
    </div>
</div>
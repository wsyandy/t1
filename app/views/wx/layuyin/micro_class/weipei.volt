{{ block_begin('head') }}
{{ weixin_css('swiper.min.css','question.css') }}
{{ block_end() }}
<div class="home_header">
    <div class="home_header_search"><span>搜索课程／直播间／老师</span></div>
</div>
<div class="home_tab_box">
    <ul class="home_tab">
        <a href="/wx/micro_class"><li>推荐</li></a>
        <li class="cur">微培</li>
        <li>职场</li>
        <li>亲子</li>
        <li>教育</li>
        <li>理财</li>
        <li>悬疑</li>
    </ul>
</div>
<div class="weipei_list">
    <div class="weipei_img">
        <img src="">
    </div>
    <h3>UI设计</h3>
    <p>如果你无法简洁的表达你的想法，那只说明你还不够了解它。如果你无法简洁的表达你的想法，那只说明你还不够了解它。</p>
</div>
<div class="weipei_list">
    <div class="weipei_img">
        <img src="">
    </div>
    <h3>UI设计</h3>
    <p>如果你无法简洁的表达你的想法，那只说明你还不够了解它。如果你无法简洁的表达你的想法，那只说明你还不够了解它。</p>
</div>
<div class="pub_end">
    <h3>我是有底线的</h3>
    <p></p>
</div>

<div class="footer_nav">
    <div class="footer_nav_box">
        <a class="home cur">
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
        <a class="mine">
            <p>我的</p>
        </a>
    </div>
</div>

<script>
    $(function () {
        $('.home_tab li').click(function () {
            $(this).addClass('cur').siblings().removeClass('cur');
        })
    })
</script>
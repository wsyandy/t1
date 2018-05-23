{{ block_begin('head') }}
{{ weixin_css('swiper.min.css','question.css') }}
{{ weixin_js('index.js','swiper.jquery.min.js') }}
{{ block_end() }}
<div class="main_content">
    <div class="home_header">
        <div class="home_header_search"><span>搜索课程／直播间／老师</span></div>
    </div>
    <div class="home_tab_box">
        <ul class="home_tab">
            <a href="/wx/micro_class"><li class="cur">推荐</li></a>
            <a href="/wx/micro_class/weipei"><li>微培</li></a>
            <li>职场</li>
            <li>亲子</li>
            <li>教育</li>
            <li>理财</li>
            <li>悬疑</li>
        </ul>
    </div>
    <!--轮播图-->
    <div class="home_banner">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <a class="swiper-slide" href="#1"><img class="swiper-img" src="/wx/layuyin/images/index_pic.png" alt="Slide 1"></a>
                <a class="swiper-slide" href="#2"><img class="swiper-img" src="/wx/layuyin/images/index_pic.png" alt="Slide 2"></a>
                <a class="swiper-slide" href="#3"><img class="swiper-img" src="/wx/layuyin/images/index_pic.png" alt="Slide 3"></a>
            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
        </div>
    </div>
    <div class="home_classify">
        <div class="home_classify_li">
            <img src="/wx/layuyin/images/home_classify_course.png" alt="tab-img">
            <p>精品课程</p>
        </div>
        <div class="home_classify_li">
            <img src="/wx/layuyin/images/home_classify_sale.png" alt="tab-img">
            <p>分销中心</p>
        </div>
        <div class="home_classify_li">
            <img src="/wx/layuyin/images/home_classify_discount.png" alt="tab-img">
            <p>低价限时</p>
        </div>
        <div class="home_classify_li">
            <img src="/wx/layuyin/images/home_classify_free.png" alt="tab-img">
            <p>免费专区</p>
        </div>
    </div>
    <!-- 3.0考拉问答 -->
    <div class="home_question">
        <div class="home_list_title">
            <p>考拉问答</p>
            <b>查看更多</b>
        </div>
        <ul>
            <li>
                <div class="edu_question">
                    <div class="question_top">
                        <div class="question_img">
                            <img src="">
                        </div>
                        <h3 class="line_hide">考研</h3>
                    </div>
                    <div class="question_title line_hide">公务员考试科目行测和申论怎么学才好？</div>
                    <div class="question_text row_hide">用户昵称：如果你每次都打算碰运气、靠前一两个星期才开始看书的话，你只是在考试的第一个阶段。理论…</div>
                </div>
            </li>
            <li>
                <div class="edu_question">
                    <div class="question_top">
                        <div class="question_img">
                            <img src="">
                        </div>
                        <h3 class="line_hide">考研</h3>
                    </div>
                    <div class="question_title line_hide">公务员考试科目行测和申论怎么学才好？</div>
                    <div class="question_text row_hide">用户昵称：如果你每次都打算碰运气、靠前一两个星期才开始看书的话，你只是在考试的第一个阶段。理论…</div>
                </div>
            </li>
        </ul>
    </div>
    <div class="home_list home_list_row">
        <div class="home_list_title">
            <p>大咖精品课</p>
            <span>换一批</span>
        </div>
        <ul class="boutique_list">
            <li>
                <div class="boutique_list_img">
                    <img src="/wx/layuyin/images/index_pic.png" alt="">
                </div>
                <div class="boutique_list_text">
                    <p class="line_hide">清华名师张老师来清华名师张老师来清华名师张老师来清华名师张老师来</p>
                    <span>1241415人学习</span>
                </div>
            </li>
            <li>
                <div class="boutique_list_img">
                    <span class="label">系列</span>
                    <img src="/wx/layuyin/images/index_pic.png" alt="">
                </div>
                <div class="boutique_list_text">
                    <p class="line_hide">清华名师张老师来清华名师张老师来清华名师张老师来清华名师张老师来...</p>
                    <span>1241415人学习</span>
                </div>
            </li>
            <li>
                <div class="boutique_list_img">
                    <img src="/wx/layuyin/images/index_pic.png" alt="">
                </div>
                <div class="boutique_list_text">
                    <p class="line_hide">清华名师张老师来清华名师张老师来清华名师张老师来清华名师张老师来...</p>
                    <span>1241415人学习</span>
                </div>
            </li>
            <li>
                <div class="boutique_list_img">
                    <img src="/wx/layuyin/images/index_pic.png" alt="">
                </div>
                <div class="boutique_list_text">
                    <p class="line_hide">清华名师张老师来清华名师张老师来清华名师张老师来清华名师张老师来...</p>
                    <span>1241415人学习</span>
                </div>
            </li>
        </ul>
    </div>
    <div class="home_list hot_room_box">
        <div class="home_list_title">
            <p>热门直播间</p>
        </div>
        <ul class="boutique_list hot_room">
            <li>
                <div class="boutique_list_img index_hot_img">
                    <span class="label">系列</span>
                    <img src="" alt="">
                </div>
                <div class="boutique_list_text">
                    <p class="row_hide">清华名师张老师来清华名师张老师来清华名师张老师来清华名师张老师来清华名师张老师来...</p>
                    <b class="money">9.9元</b>
                    <span class="study_num">1241415人学习</span>
                </div>
            </li>
            <li>
                <div class="boutique_list_img index_hot_img">
                    <img src="" alt="">
                </div>
                <div class="boutique_list_text">
                    <p class="row_hide">清华名师张老师来清华名师张老师来清华名师张老师来清华名师张老师来清华名师张老师来...</p>
                    <b class="money">9.9元</b>
                    <span class="study_num">1241415人学习</span>
                </div>
            </li>
            <li>
                <div class="boutique_list_img index_hot_img">
                    <img src="" alt="">
                </div>
                <div class="boutique_list_text">
                    <p class="row_hide">清华名师张老师来清华名师张老师来清华名师张老师来清华名师张老师来清华名师张老师来...</p>
                    <b class="money">9.9元</b>
                    <span class="study_num">1241415人学习</span>
                </div>
            </li>
        </ul>
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
            <a class="mine" href="/wx/micro_class/mine">
               <p>我的</p>
            </a>
        </div>
    </div>
</div>

<script>
    var swiper = new Swiper('.swiper-container', {
        pagination: '.swiper-pagination',
        paginationClickable: true,
        autoplay:4000,
    });
    $(function(){
        $('.home_tab li').click(function(){
            $(this).addClass('cur').siblings().removeClass('cur');
        })
    })
</script>
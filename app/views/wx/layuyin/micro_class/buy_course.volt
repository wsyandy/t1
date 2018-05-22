{{ block_begin('head') }}
{{ weixin_css('course_record.css') }}
{{ weixin_js('index.js') }}
{{ block_end() }}

<div class="main_content">
    <div class="haeder_nav">
        <span class="haeder_left_back"></span>
        <span>购买记录</span>
        <span class="haeder_right_but"></span>
    </div>
    <div class="pub_collect_title">
        <ul>
            <li>拼课中</li>
            <li class="pub_collect_title_cur">购课记录</li>
        </ul>
    </div>
    <dl class="buy_course_dl">
        <dt>2017年12月</dt>
        <dd>
            <img src="" alt="">
            <div class="buy_course_box">
                <p class="line_hide"><span class="xilie">系列</span>迅速提升吸引力的就力的就打打…</p>
                <div class="buy_course_box_bom">
                    <span>2017-12-05 18:11</span>
                    <b>9.9元</b>
                </div>
            </div>
        </dd>
        <dd>
            <img src="" alt="">
            <div class="buy_course_box">
                <p class="line_hide"><span class="xilie">系列</span>迅速提升吸引力的就力的就打打…</p>
                <div class="buy_course_box_bom">
                    <span>2017-12-05 18:11</span>
                    <b>9.9元</b>
                </div>
            </div>
        </dd>
        <dt>2017年12月</dt>
        <dd>
            <img src="" alt="">
            <div class="buy_course_box">
                <p class="line_hide"><span class="xilie">系列</span>迅速提升吸引力的就力的就打打…</p>
                <div class="buy_course_box_bom">
                    <span>2017-12-05 18:11</span>
                    <b>9.9元</b>
                </div>
            </div>
        </dd>
        <dd>
            <img src="" alt="">
            <div class="buy_course_box">
                <p class="line_hide"><span class="xilie">系列</span>迅速提升吸引力的就力的就打打…</p>
                <div class="buy_course_box_bom">
                    <span>2017-12-05 18:11</span>
                    <b>9.9元</b>
                </div>
            </div>
        </dd>
        <dd>
            <img src="" alt="">
            <div class="buy_course_box">
                <p class="line_hide"><span class="xilie">系列</span>迅速提升吸引力的就力的就打打…</p>
                <div class="buy_course_box_bom">
                    <span>2017-12-05 18:11</span>
                    <b>9.9元</b>
                </div>
            </div>
        </dd>
    </dl>
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
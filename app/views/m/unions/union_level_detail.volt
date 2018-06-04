{{ block_begin('head') }}
{{ theme_css('/m/css/union_level_detail.css') }}
{{ theme_js('/m/js/resize.js') }}
{{ block_end() }}
<div class="dengji_box" id="app">
    <div class="dengji_top">
        <img src="{{ union_level_images[union.union_level] }}">
        <div class="title">
            <i class="title_left"></i>
            <span>{{ union_level_text[union.union_level] }}</span>
            <i class="title_right"></i>
        </div>
    </div>
    <div class="dengji_num_box">
        <ul>
            <li>
                <h3>总积分</h3>
                <span>{{ union.total_integrals }}</span>
            </li>
            <div class="line"></div>
            <li>
                <h3>本月积分</h3>
                <span>{{ union.current_month_integrals }}</span>
            </li>
        </ul>
    </div>
    <div class="dengji_bottom">
        <i></i>
        <a href="/m/unions/union_level_referral?sid={{ sid }}&code={{ code }}">升级攻略</a>
    </div>
</div>

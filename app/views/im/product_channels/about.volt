{{ block_begin('head') }}
{{ theme_css('/m/css/service.css') }}
{{ block_end() }}
<div class="about_us_top">
    <img src="{{ product_channel.avatar_small_url }}">
    <h3>{{ product_channel.name }}</h3>
</div>
<div class="about_us_list">
    <ul>
        <div class="banben">
            <span>当前版本</span>
            <b>{{ version }}</b>
        </div>
        <li>
            <a href="/m/product_channels/user_agreement?code={{ product_channel.code }}">用户协议<span class="arrow_right"></span></a>
        </li>
        <li>
            <a href="/m/product_channels/privacy_agreement?code={{ product_channel.code }}">隐私条款<span class="arrow_right"></span></a>
        </li>
    </ul>
</div>
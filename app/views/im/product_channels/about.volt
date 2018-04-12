
{{ block_begin('head') }}
{{ theme_css('/im/css/haya.css') }}
{{ block_end() }}

<div class="agree_page">
    <div class="agree_page_logo">
        <img src="{{ product_channel.avatar_small_url }}" alt="">
        <span>{{ product_channel.name }}</span>
    </div>
    <ul class="agree_page_ul">
        <li ><span>current version</span> <span class="right">{{ version }}</span></li>
        <li>
            <a href="/im/product_channels/user_agreement?code={{ product_channel.code }}">
                <span>user agreement</span>
            </a>
        </li>
        <li>
            <a href="/im/product_channels/privacy_agreement?code={{ product_channel.code }}">
            <span>privacy policy</span>
            </a>
        </li>
    </ul>
</div>

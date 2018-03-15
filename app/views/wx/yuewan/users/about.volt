{{ block_begin('head') }}
{{ weixin_css('index','add_wechat') }}
{{ block_end() }}


<div class="money_content">
    <div class="header">
        <div class="header_logo">
            <p @click.stop="backAction()" class="header_back"></p>
            <span>添加微信</span>
        </div>
    </div>
    <div style="height:1rem;"></div>
    <!-- 添加微信 -->
    <div class="add_wechat">
        <div class="add_wechat_top">
            <img src="{{ product_channel.avatar_small_url }}" alt="" class="logo_superman">
            <span class="add_wechat_title" >添加{{ product_channel.name }}公众号的小伙伴都贷到款了，</span>
            <div class="add_wechat_title">
                你也赶快“ <span class="font_bold">加一下</span> 吧！
            </div>
        </div>
        <div class="add_wechat_con">
            <div class="qrcode_border">
                <img src="/wx/{{ current_theme }}/images/add_wechat_border.png" alt="" class="qrcode_borders">
                <img src="{{ product_channel.weixin_qrcode_url }}" alt="" class="qrcode_conter">
            </div>

            <div class="qrcode_conter_text">
                <span>用微信搜索“{{ product_channel.name }}”或者“{{ product_channel.weixin_no }}”</span>
                <span>即可找到{{ product_channel.name }}公众号</span>
            </div>

            {#<div class="add_wechat_box">#}
                {#<div class="add_wechat_btn1">{{ product_channel.name }}</div>#}
                {#<div class="add_wechat_btn2">复制</div>#}
            {#</div>#}

            {#<div>#}
                {#<span class="down_text">v</span>#}
                {#<span class="down_text">v</span>#}
            {#</div>#}
            {#<div class="add_wechat_btn3">#}
                {#到微信去搜索#}
            {#</div>#}

        </div>
    </div>
</div>

<script>

    var opts = {
        data: {

        },
        methods: {}
    };

    var vm = XVue(opts);

</script>

{{ block_begin('head') }}
{{ theme_css('/web/css/main') }}
{{ block_end() }}

{#{{ partial('head') }}#}

<main>
    <section class="one">
        <div class="slider">
            <img class="active" src="images/pic1_03.png" alt="">
        </div>
        <div class="title">
            <h2>Hi</h2>
            <p>语音直播、连麦聊天、组队开黑</p>
        </div>
    </section>
    <section class="b-two two">
        <div class="title">
            <h2>游戏陪玩</h2>
            <h3>电竞大神带你飞，游戏再也不缺开黑小伙伴</h3>
            <p>下载{{ product_channel.name }} app,各路电竞大神陪你轻松上分，开启游戏不眠夜</p>
        </div>
        <div class="phone">
            <!-- <img src=""> -->
            <img src="images/bg_phone_01.png" alt="">
        </div>
    </section>
    <section class="three">
        <div class="bg">
            <div class="phone">
                <img src="images/bg_phone_02.png" alt="">
            </div>
            <div class="title">
                <h2>发表情</h2>
                <h3>任性聊天不打字 表情卖萌耍不停</h3>
                <p>宅男再也不怕自己嘴笨讨不了女神欢心，HI~全新原创表情帮你拉近距离，贴心又甜蜜~</p>
            </div>
            <div class="service-cat">
                <div class="item">
                    <img src="images/middle_part1_game_03.png" alt="">
                </div>
                <div class="item">
                    <img src="images/middle_part1_game_05.png" alt="">
                </div>
                <div class="item">
                    <img src="images/middle_part1_game_07.png" alt="">
                </div>
            </div>
        </div>
    </section>
</main>
<a name="download"></a>
<h2 class="download-title">安卓下载</h2>
<footer>
    <div class="downlod-code">
        <!--  <div class="item">
           <img id="download_img_ios" alt="" src="images/qrcode_ios2.png">
           <p><span class="iphone"></span>IOS下载</p>
         </div> -->
        <div class="item">
            <img id="download_img_android" alt="" src="images/qrcode_android4.png">
        </div>
    </div>
    <div class="c-info">
        <div class="left">
            <h1>产品介绍</h1>
            <p>{{ product_channel.name }}是目前时下备受年轻人欢迎的语音直播平台！</p>
            <p>拥有超高质量的语音品质，你与队友开黑的时候，不卡不掉线不延时，再也不会被骂是猪队友！</p>
            <p>当你去认识新朋友时，请首先用声音打动别人，让别人认识你，跟新朋友说一句：“Hi！”拉近彼此之间的距离，就从Hi开始！</p>
        </div>
        <div class="right">
            <h1>公司介绍</h1>
            <p>
                {{ product_channel.name }}~是上海棕熊网络科技有限公司开发的一款可以多方实时语音直播的软件，利用声音作为唯一沟通介质为用户提供沟通平台，特色的主题聊天室和互动直播玩法功能受到广大年轻人追捧，用真实的语音传递真实的情感，用轻松的方式感受长情的陪伴。{{ product_channel.name }}绝对是你值得拥有的语音直播软件。</p>
        </div>
    </div>
</footer>
<div class="about_us">
    <div class="left">
        <p>2011-2018 All Rights Reserved</p>
        <p>{{ product_channel.company_name }}版权所有</p>
        <p>{{ product_channel.icp }}</p>
    </div>
    <div class="right">
        <p>电话：{{ product_channel.service_phone }}</p>
        <p>姓名：郑帅</p>
        <p>地址:上海市宝山区新二路999弄148号2层103室</p>
    </div>
</div>

<style>
    #cnzz_stat_icon_1266857274 {
        display: none !important;
    }
</style>
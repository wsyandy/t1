{{ block_begin('head') }}
{{ theme_css("/soft_speech") }}
{{ theme_js("/jquery.min.3.2.1","/swiper.min.2.7.6","/soft_speech") }}
{{ block_end() }}

<div class="swiper-container">
    <div class="swiper-wrapper">
        <div class="swiper-slide slide1">
            <div class="slide1_left">
                <div class="ani ani1-1">
                    <h3 class=" pt5">软语音</h3>
                    <p class=" pt10">组队开黑、连麦聊天、语音直播</p>
                </div>
                <div class="download_box ">
                    <div class="qr_code ani ani1-2 ">
                        <div class="qrcode_img">
                            <img src="/web/{{ current_theme }}/images/qrcode_img.png" alt="">
                        </div>
                        <span class="qrcode_txt">扫描二维码下载</span>
                    </div>
                    <div class="download_btn">
                        <a href="#" target="_blank" class="btn ani ani1-3"> <img class="ico_download"
                                                                                 src="/web/{{ current_theme }}/images/ico_ios.png"
                                                                                 alt=""> iOS下载</a>
                        <a href="#" target="_blank" class="btn ani ani1-3"> <img class="ico_download"
                                                                                 src="/web/{{ current_theme }}/images/ico_android.png"
                                                                                 alt="">
                            安卓下载</a>
                    </div>
                </div>
            </div>
            <div class="video_box ani ani1-4">
                <video class="video_demo" autoplay controls width="380" height="360">
                    <source src="http://www.runoob.com/try/demo_source/movie.mp4" type="video/mp4">
                    <source src="http://www.runoob.com/try/demo_source/movie.ogg" type="video/ogg">
                    <source src="http://www.runoob.com/try/demo_source/movie.webm" type="video/webm">
                    <object data="http://www.runoob.com/try/demo_source/movie.mp4" width="380" height="360">
                        <embed src="http://www.runoob.com/try/demo_source/movie.swf" width="380" height="360">
                    </object>
                </video>
            </div>

        </div>
        <div class="swiper-slide slide2">
            <div class="slide2_left ani ani2-1">
                <img class="slide2_img" src="/web/{{ current_theme }}/images/slide2_img.png" alt="">
            </div>
            <div class="slide2_right ani ani2-2">
                <h3>组队开黑</h3>
                <p>拥有超高质量的语音质量，不开不掉不延时，随时随地组队开黑</p>
            </div>

        </div>
        <div class="swiper-slide slide3">
            <div class="slide3_left ani ani3-2">
                <h3>连麦聊天</h3>
                <p>开心畅聊，让失眠的夜，不再寂寞，不再孤单</p>
            </div>
            <div class="slide3_right ani ani3-1">
                <img class="slide3_img" src="/web/{{ current_theme }}/images/slide3_img.png" alt="">
            </div>
        </div>
        <div class="swiper-slide slide4">
            <div class="slide4_top ani ani4-1">
                <h3>产品介绍</h3>
                <p> 软语音是上海棕熊网络科技有限公司开发的一款可以多方实时语音直播的软件，利用声音作为唯一沟通介质，为用户提供沟通平台，特色的主题聊天室和互动直播玩法功能受到广大年轻人追捧，用真实的语音传递真实的
                    情感，用轻松的方式感受长情的陪伴。</p>
                <p>软语音--绝对是你值得拥有的语音直播软件。</p>
            </div>
            <div class="slide4_foot ani ani4-2">
                <div class="address">
                    <span>电话：{{ product_channel.service_phone }}</span>
                    <span>地址：上海市宝山区新二路999弄148号2层103室</span>

                </div>
                <div class="reserved">
                    <span>2011-2018All Rights Reserved</span>
                    <span>{{ product_channel.company_name }}版权所有</span>
                    <span>{{ product_channel.icp }}</span>

                </div>


            </div>
        </div>

    </div>
</div>
<div class="pagination"></div>


<script>
    var opts = {
        data: {},
        methods: {}
    };

    vm = XVue(opts);

</script>
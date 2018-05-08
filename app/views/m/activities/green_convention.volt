{{ block_begin('head') }}
{{ theme_js('/m/js/resize.js') }}
{{ theme_css('/m/activities/css/green_convention.css') }}
{{ block_end() }}
<div id="app" class="vueBox">
    <div class="banner">
        <img :src="banner" alt="">
    </div>
    <div class="green_haeder">
        <img :src="green_haeder" alt="">
    </div>
    <div class="convention">
        <img  class="ico_wave wave_left" :src="ico_wave" alt="">
        <ul class="list">
            <li v-for="(item,index) in convention">
                <p class="num" v-text="index+1"></p>
                <div class="txt" v-text="item"> </div>
            </li>
        </ul>
        <img  class="ico_wave wave_right" :src="ico_wave" alt="">
    </div>
    <div class="punish_box">
        <div class="punish_title">
            <img class="title_bg" :src="title_bg" alt="">
            <span>处理方法</span>
        </div>
        <ul class="list">
            <li v-for="(item,index) in punish">
                <p class="num" v-text="index+1"></p>
                <div class="txt" v-text="item"> </div>
            </li>
        </ul>
    </div>
    <div class="footer">
        <img class="footer_bg" :src="footer_bg" alt="">
        <span>
                感谢各位对小Hi的支持，让我们共同在Hi语音营造更健康，温暖的交友氛围~
            </span>
    </div>
    <div class="footer foot">
        <span>该制度自发布日起生效最终解释权归平台所有</span>
    </div>
</div>
<script>
    var opts = {
        data: {
            banner:'/m/activities/images/banner.png',
            green_haeder:'/m/activities/images/green_haeder.png',
            ico_wave:'/m/activities/images/ico_wave.png',
            title_bg:'/m/activities/images/title_bg.png',
            footer_bg:'/m/activities/images/footer_bg.png',
            curIdx:0,
            convention:[
                '严禁发表反党反政府的言论，或做出侮辱诋毁党和国家的行为；',
                '严禁传播违反国家安全，破坏国家团结等法律，行政法规禁止的内容；',
                '严禁上传及发布低俗违规涉黄涉毒涉赌的头像、昵称、信息等行为；',
                '严禁直接或间接传播淫秽，色情信息，或进行淫秽，色情相关表演；',
                '严禁低俗、性感、引诱、挑逗性内容，着装暴露，性暗示等引诱用户付费；',
                '严禁恶意刷屏、骂人、侵犯他人隐私等不友善行为；',
                '严禁宣扬暴力行为，暴力场景，武器使用等言论；',
                '严禁进行各类广告宣传，传播带有商业广告，账号等信息的内容，包含推销商品，其他应用，网店，频道号，微信号，QQ号等；',
                '严禁组织以骚扰、恶作剧为目的的团体；'
            ],
            punish:[
                'Hi语音对上述行为实施从严标准。所有用户，若言论和行为有违反上述内容的，将会视情节轻重予以警告、3天、7天、30天封号或永久封号处理，对情节十分严重者我们会保留法律追究责任，并上报有关部门备案；',
                ' 任何用户都可以举报违规用户，但发现恶意举报投诉，我们也将对恶意举报投诉的用户进行相应的处罚。'
            ]
        },
        created: function () {

        },
        methods: {
            selectFaq:function(index){
                this.curIdx=index;
                window.location.href='details.html?'+index
            }
        }
    };
    vm = XVue(opts);
</script>

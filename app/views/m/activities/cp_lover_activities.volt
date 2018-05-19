
{{ block_begin('head') }}
{{ theme_css('/m/activities/css/cp_lover_activities.css') }}
{{ theme_js('/m/js/resize.js') }}
{{ block_end() }}
<div id="app">
    <div class="banner_box">
        <img class="banner" src="/m/activities/images/cp_lover_activities/banner.png" alt="">
        <img class="arc_line" src="/m/activities/images/cp_lover_activities/arc_line.png" alt="">
    </div>
    <img class="notice" src="/m/activities/images/cp_lover_activities/notice.png" alt="">
    <div class="extend_title">  <span>活动奖品</span>  </div>
    <div class="extend_prize">
        <ul class="prize_list">
            <li v-for="(prize,index) in prizeList">
                <div class="prize_img" :class="{'prize_img_first':index==0}">
                    <img class="prize_ico" :src="prize.ico" alt="">
                </div>
                <div class="prize_txt">
                    <p v-text="prize.txt"></p>
                    <p v-text="prize.txt1?prize.txt1:''"></p>
                    <p v-text="prize.txt2?prize.txt2:''"></p>
                </div>
            </li>
        </ul>
        <ul class="rose_list">
            <li v-for="rose in prizeImg">
                <img :src="rose" alt="">
            </li>
        </ul>
    </div>
    <div class="prize_tips">
        <p>以上礼物皆为全服唯一限定礼物 </p>
        <p>获奖用户请添加客服QQ号：3407150190</p>
    </div>
    <div class="extend_title">  <span>情侣值排行榜</span>  </div>
    <div class="lovers_list">
        <ul class="cp_list">
            <li v-for="(cp,i) in cpList"  :class=" [i==0 && 'cp_first' || i==1 && 'cp_second' || i==2 && 'cp_third' ]" >
                <div class="cp_num" v-text="'NO.'+(i+1)"></div>
                <div class="cp_avatar_box">
                    <div class="cp_avatar">
                        <img :src="cp.another.avatar" alt="">
                    </div>
                    <img  class="cp_heart"  v-if="" :src=" i==0 && cp_heart || i==1 && cp_heart1 || i==2 && cp_heart2 || i>2 && cp_heart3 " alt="">
                    <div class="cp_avatar">
                        <img :src="cp.other.avatar" alt="">
                    </div>
                </div>
                <div class="cp_name">
                    <p class="cp_name_left" v-text="cp.another.name"></p>
                    <span class="symbol_and" v-text="i?'&':''"></span>
                    <p  class="cp_name_right" v-text="cp.other.name"></p>
                </div>
            </li>
        </ul>
        <div class="your_cp_value">
            <span>您的情侣值为</span>
            <div class="cp_value">
                <img  class="cp_heart" src="/m/activities/images/cp_lover_activities/cp_heart.png" alt="">
                <span>4368</span>
            </div>
            <span>暂未上榜</span>
        </div>
    </div>
    <div class="couple_tips">
        <div class="couple_tips_title">
            如何结为情侣？
        </div>
        <ul class="tips_list">
            <li v-for="(tip,i) in tipsList">
                <span class="tip_dot"  ></span>
                <p v-text="tip"></p>
            </li>
        </ul>
        <div class="tips_foot">
            <p>把你们的情侣证分享出去</p>
            <p>撒狗粮吧！</p>
        </div>

    </div>
    <div class="extend_title">  <span>活动规则</span>  </div>
    <div class="rules_box">
        <ul class="rules_list">
            <li v-for="rule in rulesList">
                <p v-text="rule"></p>
            </li>
        </ul>

    </div>

    <div class="footer">
        <span>活动最终解释权归Hi语音官方团队</span>
    </div>



</div>

</body>
</html>
<script>
    var opts = {
        el: "#app",
        data: {
            cp_heart:'/m/activities/images/cp_lover_activities/cp_heart.png',
            cp_heart1:'/m/activities/images/cp_lover_activities/cp_heart1.png',
            cp_heart2:'/m/activities/images/cp_lover_activities/cp_heart2.png',
            cp_heart3:'/m/activities/images/cp_lover_activities/cp_heart3.png',
            prizeList:[
                {
                    ico:'/m/activities/images/cp_lover_activities/ico_first.png',
                    txt:'5月21日13点14分全服公告爱的宣言',
                    txt1:'情侣靓号520XXXX]一对',
                    txt2:'冠军限定神秘大礼'
                },
                {
                    ico:'/m/activities/images/cp_lover_activities/ico_second.png',
                    txt:'亚军神秘礼物'
                },
                {
                    ico:'/m/activities/images/cp_lover_activities/ico_third.png',
                    txt:'季军神秘礼物'
                },
            ],
            prizeImg:['/m/activities/images/cp_lover_activities/rose_gold.png','/m/activities/images/cp_lover_activities/rose_silver.png','/m/activities/images/cp_lover_activities/rose_red.png',],
            cpList:[
                {
                    value:123456,
                    another:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'小胖子乖乖',
                        id:'1314520',
                    },
                    other:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'王潇肃的猫',
                        id:'120291',
                    },
                },
                {
                    value:654321,
                    another:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'小胖子乖乖',
                        id:'1314520',
                    },
                    other:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'章鱼吉娃娃',
                        id:'120293',
                    },
                },
                {
                    value:123456,
                    another:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'小胖子乖乖',
                        id:'1314520',
                    },
                    other:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'王潇肃的猫',
                        id:'120291',
                    },
                },
                {
                    value:654321,
                    another:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'小胖子乖乖小胖子乖乖小胖子乖乖',
                        id:'1314520',
                    },
                    other:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'章鱼吉娃娃章鱼吉娃娃章鱼吉娃娃章鱼吉娃娃',
                        id:'120293',
                    },
                },
                {
                    value:123456,
                    another:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'小胖子乖乖',
                        id:'1314520',
                    },
                    other:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'王潇肃的猫',
                        id:'120291',
                    },
                },
                {
                    value:654321,
                    another:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'小胖子乖乖',
                        id:'1314520',
                    },
                    other:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'章鱼吉娃娃',
                        id:'120293',
                    },
                },
                {
                    value:123456,
                    another:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'小胖子乖乖',
                        id:'1314520',
                    },
                    other:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'王潇肃的猫',
                        id:'120291',
                    },
                },
                {
                    value:654321,
                    another:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'小胖子乖乖小胖子乖乖小胖子乖乖',
                        id:'1314520',
                    },
                    other:{
                        avatar:'/m/activities/images/cp_lover_activities/avatar.png',
                        name:'章鱼吉娃娃章鱼吉娃娃章鱼吉娃娃章鱼吉娃娃',
                        id:'120293',
                    },
                },

            ],
            tipsList:[
                '房主发起“处CP”，房主默认为情侣中的一方',
                '房主发起“处CP”后，房间内会出现“情侣图标”另一半点击即可进入情侣厅。',
                '双方在情侣厅内点击“同意”，即可成为情侣，生成情侣证',
            ],
            rulesList:[
                '1. 情侣之间互相赠送礼物，每赠送一个钻石，用户的情侣值+1',
                '2. 活动时间为5月20日 00:00 — 5月20日 23:59',
            ]


        },
        created() {

        },
        methods: {
            navToDetails:function(){
                window.location.href='details.html'
            }
        }

    }

var vm = XVue(opts);
</script>

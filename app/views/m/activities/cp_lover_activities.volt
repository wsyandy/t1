<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ title }}</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/m/activities/css/cp_lover_activities.css">
</head>
<body>
<div class="vueBox">
    <div class="banner_box">
        <img class="banner" src="images/banner.png" alt="">
        <img class="arc_line" src="images/arc_line.png" alt="">
    </div>
    <img class="notice" src="images/notice.png" alt="">
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
                <img  class="cp_heart" src="images/cp_heart.png" alt="">
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

<script src="js/vue.min.2.5.13.js"></script>
<script src="js/vue.index.js"></script>
<script src="js/resize.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>砸金蛋</title>
    <link rel="stylesheet" href="/m/rotary_draw_histories/css/main.css">
    <script>
        (function(doc, win) {
            var docEl = doc.documentElement,
                resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
                recalc = function() {
                    var clientWidth = docEl.clientWidth;
                    if (!clientWidth) return;
                    docEl.style.fontSize = 100 * (clientWidth / 750) + 'px';
                };

            if (!doc.addEventListener) return;
            win.addEventListener(resizeEvt, recalc, false);
            doc.addEventListener('DOMContentLoaded', recalc, false);
        })(document, window);
    </script>
</head>
<body>
<div id="app" class="gold_egg">
    <div class="gold_egg_banner"> <span>活动时间：5月1－3</span></div>
    <div class="gold_egg_reward">
        <div class="gold_egg_rewardli">
            <span class="gold"></span>
            <span class="text">金币× ？</span>
        </div>
        <div class="gold_egg_rewardli">
            <span class="diamond"></span>
            <span class="text">钻石× ？</span>
        </div>
    </div>
    <div class="gold_egg_box">
        <div :class="{'egg_gif_gifafter':isLottery,'egg_gif_start':true}"></div>
        <span class="gold_egg_box_hint">10钻石／次</span>
    </div>
    <div class="gold_egg_butbox">
        <div @click="smashEggs(1)" class="gold_egg_butboxli"><span>砸蛋一个</span></div>
        <div @click="smashEggs(10)" class="gold_egg_butboxli ten_buttom"><span>砸蛋十个</span></div>
    </div>
    <div class="gold_egg_marquee">
        <ul class="gold_egg_marquee_ul" :class="{marquee_top:animate}">
            <li v-for="(item, index) in marqueeList">
                <span>恭喜“{{item.name}}的大”砸中了</span>
                <span class="gold">100钻石</span>
            </li>
        </ul>
    </div>
    <div class="gold_egg_rules">
        <p>活动规则：</p>
        <span>1.10钻获得一次砸金蛋机会</span>
        <span>2.抽中的钻石数将会直接放入您的账户。</span>
        <p>奖品：</p>
        <span>1.金币x100、金币x1000</span>
        <span>2.钻石x10、钻石x30、钻石x100、钻石x500、</span>
    </div>
    <div class="gold_eggmy_prize">
        <span>我的奖品</span>
    </div>

    <div v-if="isHintToast" class="not_balance_toast">
        <b>提示</b>
        <span class="hint">您的钻石余额不足，请先充值</span>
        <div class="not_balance_box">
            <span @click="topupBalance(false)" class="cancel">取消</span>
            <span @click="topupBalance(true)"  class="topup">充值</span>
        </div>
    </div>

    <div v-if="isResultsToast" class="winning_results_toast">
        <span v-if="resultsState==0" class="gold_bigicon"></span>
        <span  v-if="resultsState==1" class="diamond_bigicon"></span>
        <span  v-if="resultsState<=1" class="winning_results_text">获得10{{resultsState==0?'金币':'钻石'}}</span>
        <div  v-if="resultsState==2" class="winning_results_ulbox">
            <ul class="winning_results_ul">
                <li><span>获得钻石</span><span class="diamond">＋10</span></li>
                <li><span>获得钻石</span><span class="gold">＋10</span></li>
                <li><span>获得钻石</span><span class="diamond">＋10</span></li>
                <li><span>获得钻石</span><span class="diamond">＋10</span></li>
                <li><span>获得钻石</span><span class="diamond">＋10</span></li>
                <li><span>获得钻石</span><span class="gold">＋10</span></li>
                <li><span>获得钻石</span><span class="gold">＋10</span></li>
                <li><span>获得钻石</span><span class="diamond">＋10</span></li>
                <li><span>获得钻石</span><span class="gold">＋10</span></li>
                <li><span>获得钻石</span><span class="diamond">＋10</span></li>
            </ul>
        </div>
        <div @click="closeResults" class="winning_results_buttom"><span>确定</span></div>
    </div>
    <div v-if="isHintToast || isResultsToast" class="mask_box"></div>
</div>

<script src="/js/vue/2.0.5/vue.min.js"></script>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            isLottery:false,
            isHintToast:false,
            isResultsToast:false,
            //开奖状态：0为单抽获得金币、1为单抽获得钻石、2十连抽
            resultsState:0,
            animate: false,
            marqueeList:[
                {
                    name: '1军ddd',
                },
                {
                    name: '2军',
                },
                {
                    name: '3军',
                },
                {
                    name: '4军',
                }
            ]
        },
        mounted:function(){
            setInterval(this.showMarquee, 2000)
        },
        methods: {
            smashEggs:function(num){
                var self = this;
                switch(num){
                    case 1:
                        this.isLottery = !this.isLottery;
                        break;
                    case 10:
                        this.isLottery = !this.isLottery;
                        break;
                }
                this.times = setTimeout(function(){
                    if(num==1){
                        self.isLottery = false;
                        self.isResultsToast = true;
                    }else{
                        self.isLottery = false;
                        self.isResultsToast = true;
                        self.resultsState = 2;
                    }
                    clearTimeout(self.times)
                },3000);
            },
            closeResults:function(){
                this.isResultsToast = false;
            },
            topupBalance:function(type){
                this.isHintToast = false;
                if(type){
                    console.log('去充值')
                }else{
                    console.log('取消')
                }
            },
            showMarquee: function () {
                this.animate = true;
                setTimeout(()=>{
                    this.marqueeList.push(this.marqueeList[0]);
                this.marqueeList.shift();
                this.animate = false;
            },500)},
        }
    })
</script>
</body>
</html>
{{ block_begin('head') }}
{{ theme_css('/m/activities/css/ranking_to_hot_activity') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>

    <div id="activity_ranking" class="activity_ranking">
        <div class="activity_ranking_top"></div>
        <div class="activity_reward"></div>
        <div class="ranking_list_title">
            <span>周榜</span>
        </div>
        <ul class="module_list">
            <li>
                <p class="title">魅力榜</p>
                <p class="module_list_box"><span>第一名</span><i>新礼物一周冠名</i></p>
            </li>
            <li>
                <p class="title">贡献榜</p>
                <p class="module_list_box"><span>第一名</span><i>一周热门活动推荐位</i></p>
            </li>
        </ul>
        <div class="ranking_list_title">
            <span>日榜</span>
        </div>
        <ul class="module_list">
            <li>
                <p class="title">贡献榜</p>
                <p class="module_list_box"><span>第一名</span><i>一天热门活动推荐位</i></p>
            </li>
        </ul>
        <div class="ranking_list_title">
            <span>活动规则</span>
        </div>
        <div class="activity_rules">
            <p><span>1、</span><span>用户在活动期间送出礼物，每送出1个钻石礼物，送出用户贡献值+1，收到礼物用户魅力值+1;</span></p>
            <p><span>2、</span><span>周榜魅力值第一的用户获得新礼物一周的冠名权，周榜贡献值第一的用户获得一周热门活动推荐位；每日贡献排行第一的用户获得一天热门活动推荐位；</span></p>
            <p><span>3、</span><span>活动时间为2018年4月9日0时0分——2018年4月16日0时0分;</span></p>
            <p><span>4、</span><span>获奖用户请联系官方客服QQ：3407150190领取奖励。</span></p>
        </div>
        <p class="activity_bottom_text">活动最终解释权归Hi语音官方团队</p>
    </div>
</div>

<script>
    var opts = {
        data: {},
        methods: {}
    };

    vm = XVue(opts);
</script>
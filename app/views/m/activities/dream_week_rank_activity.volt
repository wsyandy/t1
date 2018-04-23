{{ block_begin('head') }}
{{ theme_css('/m/activities/css/dream_week_rank_activity_1') }}
{{ block_end() }}
<div id="await_player" class="gift_online">
    <img class="gift_online_banner" src="/m/activities/images/gift_online_banner.png" alt="">
    <ul class="gift_online_introduce">
        <li>
            <img src="/m/activities/images/gift_highheels.png" alt="高跟鞋"/>
            <p>世间有一双水晶鞋，穿上它的人夜晚会做甜蜜蜜的梦梦中，草莓棉花糖组成蜜粉色的树灯火辉煌的宫殿里，舞会已经开场而你就是真正的公主</p>
        </li>
        <li>
            <img src="/m/activities/images/gift_handcatenary.png" alt="手链"/>
            <p>倾世的美人潘多拉，她离开奥林匹斯的时候，一定落下滚滚的泪珠泪珠儿串起成手链寂寞的等待有缘的人</p>
        </li>
        <li>
            <img src="/m/activities/images/gift_rolex.png" alt="劳力士"/>
            <p>劳力士是沉稳的，成熟的，寡言的年仅24岁的威尔斯多夫只身前往伦敦漂泊异乡忍受嘲讽多年后，ROLEX诞生</p>
        </li>
        <li>
            <img src="/m/activities/images/gift_diamondring.png" alt="钻石"/>
            <p>钻石是承诺，因经年累月的温柔陪伴凝聚而出真心浮世万千，吾爱有三。日、月与卿。日为朝，月为暮，卿为朝朝暮暮。</p>
        </li>
    </ul>
    <p class="gift_online_title">Hi平台新礼物上线</p>
    <div class="gift_online_box">
        <div class="gift_online_boxli">
            <img src="/m/activities/images/gift_highheels_img.png" alt="水晶鞋">
            <span>水晶鞋</span>
        </div>
        <div class="gift_online_boxli">
            <img src="/m/activities/images/gift_handcatenary_img.png" alt="潘多拉手链">
            <span>潘多拉手链</span>
        </div>
        <div class="gift_online_boxli">
            <img src="/m/activities/images/gift_rolex_img.png" alt="劳力士">
            <span>劳力士</span>
        </div>
        <div class="gift_online_boxli">
            <img src="/m/activities/images/gift_diamondring_img.png" alt="钻戒">
            <span>钻戒</span>
        </div>
    </div>
    <p class="gift_online_title">活动奖励</p>
    <div class="gift_online_wirebox">
        <p class="title_pos">周榜</p>
        <p class="title_type">魅力榜</p>
        <ul class="gift_ranking_ul">
            <li>
                <b>1st</b>
                <span>礼物一周冠名权</span>
            </li>
            <li>
                <i>2st</i>
                <span>神秘新座驾15天</span>
            </li>
            <li>
                <i>3st</i>
                <span>兰博基尼15天</span>
            </li>
        </ul>
    </div>
    <div class="gift_online_wirebox gift_online_nowirebox">
        <p class="title_type">贡献榜</p>
        <ul class="gift_ranking_ul">
            <li>
                <b>1st</b>
                <span>一周热门推荐位</span>
            </li>
            <li>
                <i>2st</i>
                <span>神秘新座驾15天</span>
            </li>
            <li>
                <i>3st</i>
                <span>兰博基尼15天</span>
            </li>
        </ul>
    </div>
    <p class="gift_online_title">活动规则</p>
    <div class="gift_online_rules">
        <p><span>1、</span><span>魅力周榜第一名奖励为一个神秘新礼物的冠名权，贡献周榜第一名用户会获得首页热门推荐banner位置一周;</span></p>
        <p><span>2、</span><span>用户在活动期间送出礼物，每送出1个钻石礼物，送出用户的贡献值    ，收到礼物用户的魅力值；</span></p>
        <p><span>3、</span><span>活动设有魅力周榜和贡献周榜。实时榜单请参考排行榜的魅力周榜，贡献周榜;</span></p>
        <p><span>4、</span><span>活动时间为2018年4月16日0时0分——2018年4月22日23时59分；</span></p>
        <p><span>5、</span><span>获奖用户请联系官方客服QQ：3407150190领取奖励；</span></p>
        <p><span>6、</span><span>活动结果将会在每周一14:00公布，请保持关注。</span></p>
    </div>

    <!-- 新加结果 -->
    <div class="activity_results">
        <div class="activity_results_title">
            <div class="title_text">
                <span>魅力榜</span>
            </div>
        </div>
        <div class="activity_results_box">
            <div class="activity_results_box_list">
                <span class="neo"></span>
                <span class="two"></span>
                <span class="three"></span>
                <span class="line"></span>
            </div>
            <ul class="activity_results_boxul">
                {% for user in charm_users %}
                    <li>
                        <span class="name">{{ user.nickname }}</span>
                        <span>ID：{{ user.uid }}</span>
                    </li>
                {% endfor %}
            </ul>
        </div>
        <div class="activity_results_title" style="margin-top: 38px;">
            <div class="title_text">
                <span>贡献榜</span>
            </div>
        </div>
        <div class="activity_results_box">
            <div class="activity_results_box_list">
                <span class="neo"></span>
                <span class="two"></span>
                <span class="three"></span>
                <span class="line"></span>
            </div>
            <ul class="activity_results_boxul">
                {% for user in wealth_users %}
                    <li>
                        <span class="name">{{ user.nickname }}</span>
                        <span>ID：{{ user.uid }}</span>
                    </li>
                {% endfor %}
            </ul>
        </div>
    </div>
    <!-- 新加结果 -->

    <p class="gift_online_bottom">活动最终解释权归Hi语音官方团队</p>
</div>
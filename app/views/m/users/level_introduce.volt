{{ block_begin('head') }}
{{ theme_css('/m/css/glory.css') }}
{{ block_end() }}
<div class="vueBox" id="app" v-cloak="">
    <div class="glory_head">
        <img class="glory_bg" :src="gloryBg" alt="">
        <div class="glory_my">
            <img class="glory_icon" :src="gloryList[level].icon" alt="">
            <span class="glory_name">${ gloryList[level].name }</span>
            <div class="glory_tips" v-show="level < 35">
                <span>还需</span>
                <span class="glory_val">${ need_experience }</span>
                <span class="glory_val">荣耀值</span>
                <span>升级为${gloryList[level+1].name}</span>
            </div>

        </div>
    </div>

    <div class="glory_next" v-show="level < 35">
        <div class="glory_next_title">
            <span class="line_vertical"></span>
            <span>下一等级特权</span>
        </div>
        <div class="glory_next_info">
            <img class="glory_icon" :src="gloryList[level+1].icon" alt="">
            <span class="glory_name">${gloryList[level+1].name}</span>
        </div>
    </div>
    <a class="glory_details" href="/m/users/level_detail?sid={{ sid }}&code={{ code }}">
        <div class="glory_details_title">
            <span class="line_vertical"></span>
            <span>Hi荣耀等级介绍</span>
        </div>
        <img class="arrow_right" :src="arrowRight" alt="">
    </a>
</div>


<script>
    var opts = {
        data: {
            level: {{ level }},
            need_experience: {{ need_experience }},
            gloryBg: "/m/images/glory_bg.png",
            arrowRight: "/m/images/arrow_right.png",
            gloryList: [
                {
                    level: 0,
                    icon: "/m/images/glory_no.png",
                    name: "无荣耀 ",
                    value: 100
                },
                {
                    level: 1,
                    icon: "/m/images/level_1.png",
                    name: "青铜Ⅰ ",
                    value: 100
                },
                {
                    level: 2,
                    icon: "/m/images/level_2.png",
                    name: "青铜Ⅱ",
                    value: 300
                },
                {
                    level: 3,
                    icon: "/m/images/level_3.png",
                    name: "青铜Ⅲ  ",
                    value: 500
                },
                {
                    level: 4,
                    icon: "/m/images/level_4.png",
                    name: "青铜Ⅳ  ",
                    value: 700
                },
                {
                    level: 5,
                    icon: "/m/images/level_5.png",
                    name: "青铜Ⅴ  ",
                    value: 900
                },
                {
                    level: 6,
                    icon: "/m/images/level_6.png",
                    name: "白银Ⅰ ",
                    value: 1200
                },
                {
                    level: 7,
                    icon: "/m/images/level_7.png",
                    name: "白银Ⅱ  ",
                    value: 1400
                },
                {
                    level: 8,
                    icon: "/m/images/level_8.png",
                    name: "白银Ⅲ  ",
                    value: 1600
                },
                {
                    level: 9,
                    icon: "/m/images/level_9.png",
                    name: "白银Ⅳ  ",
                    value: 1800
                },
                {
                    level: 10,
                    icon: "/m/images/level_10.png",
                    name: "白银Ⅴ  ",
                    value: 2000
                },
                {
                    level: 11,
                    icon: "/m/images/level_11.png",
                    name: "黄金Ⅰ ",
                    value: 2200,
                    reward: "送7位靓号或者6位普通号一个"
                },
                {
                    level: 12,
                    icon: "/m/images/level_12.png",
                    name: "黄金Ⅱ  ",
                    value: 2400
                },
                {
                    level: 13,
                    icon: "/m/images/level_13.png",
                    name: "黄金Ⅲ  ",
                    value: 2600
                },
                {
                    level: 14,
                    icon: "/m/images/level_14.png",
                    name: "黄金Ⅳ  ",
                    value: 2800
                },
                {
                    level: 15,
                    icon: "/m/images/level_15.png",
                    name: "黄金Ⅴ  ",
                    value: 3000
                },
                {
                    level: 16,
                    icon: "/m/images/level_16.png",
                    name: "铂金Ⅰ ",
                    value: 4000,
                    reward: "送6位靓号或者7位高级靓号一个"
                },
                {
                    level: 17,
                    icon: "/m/images/level_17.png",
                    name: "铂金Ⅱ  ",
                    value: 0
                },
                {
                    level: 18,
                    icon: "/m/images/level_18.png",
                    name: "铂金Ⅲ  ",
                    value: 0
                },
                {
                    level: 19,
                    icon: "/m/images/level_19.png",
                    name: "铂金Ⅳ  ",
                    value: 0
                },
                {
                    level: 20,
                    icon: "/m/images/level_20.png",
                    name: "铂金Ⅴ  ",
                    value: 0
                },
                {
                    level: 21,
                    icon: "/m/images/level_21.png",
                    name: "钻石Ⅰ ",
                    value: 10000,
                    reward: "送5位高级靓号或者4位靓号一个"
                },
                {
                    level: 22,
                    icon: "/m/images/level_22.png",
                    name: "钻石Ⅱ  ",
                    value: 0
                },
                {
                    level: 23,
                    icon: "/m/images/level_23.png",
                    name: "钻石Ⅲ  ",
                    value: 0
                },
                {
                    level: 24,
                    icon: "/m/images/level_24.png",
                    name: "钻石Ⅳ  ",
                    value: 0
                },
                {
                    level: 25,
                    icon: "/m/images/level_25.png",
                    name: "钻石Ⅴ  ",
                    value: 0
                },
                {
                    level: 26,
                    icon: "/m/images/level_26.png",
                    name: "王者Ⅰ ",
                    value: 20000,
                    reward: "送5位高级靓号或者4位靓号一个"
                },
                {
                    level: 27,
                    icon: "/m/images/level_27.png",
                    name: "王者Ⅱ  ",
                    value: 0
                },
                {
                    level: 28,
                    icon: "/m/images/level_28.png",
                    name: "王者Ⅲ  ",
                    value: 0
                },
                {
                    level: 29,
                    icon: "/m/images/level_29.png",
                    name: "王者Ⅳ  ",
                    value: 0
                },
                {
                    level: 30,
                    icon: "/m/images/level_30.png",
                    name: "王者Ⅴ  ",
                    value: 0
                },
                {
                    level: 31,
                    icon: "/m/images/level_31.png",
                    name: "星耀Ⅰ ",
                    value: 0,
                    reward: "送4位高级靓号或者3位普通号一个"
                },
                {
                    level: 32,
                    icon: "/m/images/level_32.png",
                    name: "星耀Ⅱ  ",
                    value: 0
                },
                {
                    level: 33,
                    icon: "/m/images/level_33.png",
                    name: "星耀Ⅲ  ",
                    value: 30000
                },
                {
                    level: 34,
                    icon: "/m/images/level_34.png",
                    name: "星耀Ⅳ  ",
                    value: 0
                },
                {
                    level: 35,
                    icon: "/m/images/level_35.png",
                    name: "星耀Ⅴ  ",
                    value: 0
                }
            ]
        },
        methods: {}
    };

    vm = XVue(opts);
</script>

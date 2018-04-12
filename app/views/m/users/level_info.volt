{{ block_begin('head') }}
{{ theme_css('/m/ruanyuyin/css/level_introduce.css') }}
{{ block_end() }}
<div class="wo_dengji" id="app">
    <div>
        <img :src="glory_list[current_level].icon">
        <h3>${segment_text}</h3>
        <p v-if="show_upgrade_official" v-show="current_level < 35">还需<span>${ need_experience }荣耀值</span>升级为<span>${ glory_list[current_level+1].name }</span>
        </p>
    </div>
    <div class="wo_box" id>
        <div v-if="show_upgrade_official">
            <div class="box_list">
                <i class="icon_01"></i>
                <p>下一等级特权：<b>${ glory_list[current_level+1].name }等级勋章</b></p>
            </div>
        </div>
        <div class="box_list" @click="glory_list_referral()">
            <i class="icon_02"></i>
            <p>软语音荣耀等级介绍</p>
            <span></span>
        </div>
    </div>
</div>
<script>
    var opts = {
        data: {
            current_user_sid: "{{ sid }}",
            current_level: {{ level }},
            segment_text: "{{ segment_text }}",
            need_experience: "{{ need_experience }}",
            show_upgrade_official: "{{ show_upgrade_official }}",
            skip_url: "{{ skip_url }}",
            glory_list: [
                {
                    level: 0,
                    icon: "/m/ruanyuyin/images/t_1.png",
                    name: "无荣耀 "
                },
                {
                    level: 1,
                    icon: "/m/ruanyuyin/images/t_1.png",
                    name: "青铜Ⅰ "
                },
                {
                    level: 2,
                    icon: "/m/ruanyuyin/images/t_2.png",
                    name: "青铜Ⅱ"
                },
                {
                    level: 3,
                    icon: "/m/ruanyuyin/images/t_3.png",
                    name: "青铜Ⅲ  "
                },
                {
                    level: 4,
                    icon: "/m/ruanyuyin/images/t_4.png",
                    name: "青铜Ⅳ  "
                },
                {
                    level: 5,
                    icon: "/m/ruanyuyin/images/t_5.png",
                    name: "青铜Ⅴ  "
                },
                {
                    level: 6,
                    icon: "/m/ruanyuyin/images/y_1.png",
                    name: "白银Ⅰ "
                },
                {
                    level: 7,
                    icon: "/m/ruanyuyin/images/y_2.png",
                    name: "白银Ⅱ  "
                },
                {
                    level: 8,
                    icon: "/m/ruanyuyin/images/y_3.png",
                    name: "白银Ⅲ  "
                },
                {
                    level: 9,
                    icon: "/m/ruanyuyin/images/y_4.png",
                    name: "白银Ⅳ  "
                },
                {
                    level: 10,
                    icon: "/m/ruanyuyin/images/y_5.png",
                    name: "白银Ⅴ  "
                },
                {
                    level: 11,
                    icon: "/m/ruanyuyin /images/h_1.png",
                    name: "黄金Ⅰ ",
                    rank_class: 'h_color'

                },
                {
                    level: 12,
                    icon: "/m/ruanyuyin/images/h_2.png",
                    name: "黄金Ⅱ  "
                },
                {
                    level: 13,
                    icon: "/m/ruanyuyin/images/h_3.png",
                    name: "黄金Ⅲ  "
                },
                {
                    level: 14,
                    icon: "/m/ruanyuyin/images/h_4.png",
                    name: "黄金Ⅳ  "
                },
                {
                    level: 15,
                    icon: "/m/ruanyuyin/images/h_5.png",
                    name: "黄金Ⅴ  "
                },
                {
                    level: 16,
                    icon: "/m/ruanyuyin/images/b_1.png",
                    name: "铂金Ⅰ ",
                    rank_class: 'b_color'
                },
                {
                    level: 17,
                    icon: "/m/ruanyuyin/images/b_2.png",
                    name: "铂金Ⅱ  "
                },
                {
                    level: 18,
                    icon: "/m/ruanyuyin/images/b_3.png",
                    name: "铂金Ⅲ  "
                },
                {
                    level: 19,
                    icon: "/m/ruanyuyin/images/b_4.png",
                    name: "铂金Ⅳ  "
                },
                {
                    level: 20,
                    icon: "/m/ruanyuyin/images/b_5.png",
                    name: "铂金Ⅴ  "
                },
                {
                    level: 12,
                    icon: "/m/ruanyuyin/images/z_1.png",
                    name: "钻石Ⅰ ",
                    rank_class: 'z_color'
                },
                {
                    level: 22,
                    icon: "/m/ruanyuyin/images/z_2.png",
                    name: "钻石Ⅱ  "
                },
                {
                    level: 23,
                    icon: "/m/ruanyuyin/images/z_3.png",
                    name: "钻石Ⅲ  "
                },
                {
                    level: 24,
                    icon: "/m/ruanyuyin/images/z_4.png",
                    name: "钻石Ⅳ  "
                },
                {
                    level: 25,
                    icon: "/m/ruanyuyin/images/z_5.png",
                    name: "钻石Ⅴ  "
                },
                {
                    level: 26,
                    icon: "/m/ruanyuyin/images/w_1.png",
                    name: "王者Ⅰ ",
                    rank_class: 'w_color'
                },
                {
                    level: 27,
                    icon: "/m/ruanyuyin/images/w_2.png",
                    name: "王者Ⅱ  "
                },
                {
                    level: 28,
                    icon: "/m/ruanyuyin/images/w_3.png",
                    name: "王者Ⅲ  "
                },
                {
                    level: 29,
                    icon: "/m/ruanyuyin/images/w_4.png",
                    name: "王者Ⅳ  "
                },
                {
                    level: 30,
                    icon: "/m/ruanyuyin/images/w_5.png",
                    name: "王者Ⅴ  "
                },
                {
                    level: 31,
                    icon: "/m/ruanyuyin/images/x_1.png",
                    name: "星耀Ⅰ ",
                    rank_class: 'x_color'
                },
                {
                    level: 32,
                    icon: "/m/ruanyuyin/images/x_2.png",
                    name: "星耀Ⅱ  "
                },
                {
                    level: 33,
                    icon: "/m/ruanyuyin/images/x_3.png",
                    name: "星耀Ⅲ  "
                },
                {
                    level: 34,
                    icon: "/m/ruanyuyin/images/x_4.png",
                    name: "星耀Ⅳ  "
                },
                {
                    level: 35,
                    icon: "/m/ruanyuyin/images/x_5.png",
                    name: "星耀Ⅴ  "
                }
            ]
        },
        methods: {
            glory_list_referral: function () {
                var url = vm.skip_url + '?sid=' + vm.current_user_sid;
                vm.redirectAction(url);
            }
        }
    };
    vm = XVue(opts);
</script>

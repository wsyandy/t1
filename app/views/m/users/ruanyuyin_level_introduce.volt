{{ block_begin('head') }}
{{ theme_css('/m/ruanyuyin/css/level_introduce.css') }}
{{ block_end() }}
<div class="dengji_text" id="app">
    <h3>${title}</h3>
    <p>荣耀等级是您在软语音上尊贵身份的象征，不同的等级，在昵称前面有不同的荣耀勋章。通过赠送礼物可以快速提高您的等级，等级越高，特权越高，例如公屏消息前的等级勋章，送靓号，快来体验吧~</p>
    <p>送靓号（靓号分为普通号、靓号、高级靓号3类）活动时间有限，先到先得，快去升级吧！</p>

<div class="dengji_list">以下是软语音荣耀等级对应的名称和特权：</div>
<div class="week_list" style="display: block;">
    <table class="table">
        <tr class="week_tr_title">
            <td style="width:20%;">标志</td>
            <td style="width:30%;">等级名称</td>
            <td style="width:50%;">等级特权</td>
        </tr>
        <tr v-for="glory in glory_list">
            <td><img :src="glory.icon" alt=""></td>
            <td>
                <h5 :class="glory.rank_class?glory.rank_class:''">${ glory.name }</h5>
            </td>
            <td :class="glory.reward_class?glory.reward_class:''">
                <b :class="glory.rank_class?glory.rank_class:''">${ glory.reward?glory.reward:''}</b>
                <span>${ glory.name }荣耀勋章</span>
            </td>
        </tr>
    </table>
</div>
</div>
<script>
    var opts = {
        data: {
            title:"{{ title }}",
            glory_list: [
                {
                    icon: "/m/ruanyuyin/images/t_1.png",
                    name: "青铜Ⅰ ",
                    rank_class: ''
                },
                {
                    icon: "/m/ruanyuyin/images/t_2.png",
                    name: "青铜Ⅱ",
                    rank_class: ''
                },
                {
                    icon: "/m/ruanyuyin/images/t_3.png",
                    name: "青铜Ⅲ  ",
                    rank_class: ''
                },
                {
                    icon: "/m/ruanyuyin/images/t_4.png",
                    name: "青铜Ⅳ  ",
                    rank_class: ''
                },
                {
                    icon: "/m/ruanyuyin/images/t_5.png",
                    name: "青铜Ⅴ  ",
                    rank_class: ''
                },
                {
                    icon: "/m/ruanyuyin/images/y_1.png",
                    name: "白银Ⅰ ",
                    rank_class: 'y_color'
                },
                {
                    icon: "/m/ruanyuyin/images/y_2.png",
                    name: "白银Ⅱ  ",
                    rank_class: 'y_color'
                },
                {
                    icon: "/m/ruanyuyin/images/y_3.png",
                    name: "白银Ⅲ  ",
                    rank_class: 'y_color'
                },
                {
                    icon: "/m/ruanyuyin/images/y_4.png",
                    name: "白银Ⅳ  ",
                    rank_class: 'y_color'
                },
                {
                    icon: "/m/ruanyuyin/images/y_5.png",
                    name: "白银Ⅴ  ",
                    rank_class: 'y_color'
                },
                {
                    icon: "/m/ruanyuyin/images/h_1.png",
                    name: "黄金Ⅰ ",
                    rank_class: 'h_color',
                    reward: "送7位靓号或者6位普通号一个",
                    reward_class:'huangjin'

                },
                {
                    icon: "/m/ruanyuyin/images/h_2.png",
                    name: "黄金Ⅱ  ",
                    rank_class: 'h_color'
                },
                {
                    icon: "/m/ruanyuyin/images/h_3.png",
                    name: "黄金Ⅲ  ",
                    rank_class: 'h_color'
                },
                {
                    icon: "/m/ruanyuyin/images/h_4.png",
                    name: "黄金Ⅳ  ",
                    rank_class: 'h_color'
                },
                {
                    icon: "/m/ruanyuyin/images/h_5.png",
                    name: "黄金Ⅴ  ",
                    rank_class: 'h_color'
                },
                {
                    icon: "/m/ruanyuyin/images/b_1.png",
                    name: "铂金Ⅰ ",
                    rank_class: 'b_color',
                    reward: "送7位靓号或者6位高级靓号一个",
                    reward_class:'bojin'
                },
                {
                    icon: "/m/ruanyuyin/images/b_2.png",
                    name: "铂金Ⅱ  ",
                    rank_class: 'b_color'
                },
                {
                    icon: "/m/ruanyuyin/images/b_3.png",
                    name: "铂金Ⅲ  ",
                    rank_class: 'b_color'
                },
                {
                    icon: "/m/ruanyuyin/images/b_4.png",
                    name: "铂金Ⅳ  ",
                    rank_class: 'b_color'
                },
                {
                    icon: "/m/ruanyuyin/images/b_5.png",
                    name: "铂金Ⅴ  ",
                    rank_class: 'b_color'
                },
                {
                    icon: "/m/ruanyuyin/images/z_1.png",
                    name: "钻石Ⅰ ",
                    rank_class: 'z_color',
                    reward: "送6位高级靓号或者5位靓号一个",
                    reward_class:'zuanshi'
                },
                {
                    icon: "/m/ruanyuyin/images/z_2.png",
                    name: "钻石Ⅱ  ",
                    rank_class: 'z_color'
                },
                {
                    icon: "/m/ruanyuyin/images/z_3.png",
                    name: "钻石Ⅲ  ",
                    rank_class: 'z_color'
                },
                {
                    icon: "/m/ruanyuyin/images/z_4.png",
                    name: "钻石Ⅳ  ",
                    rank_class: 'z_color'
                },
                {
                    icon: "/m/ruanyuyin/images/z_5.png",
                    name: "钻石Ⅴ  ",
                    rank_class: 'z_color'
                },
                {
                    icon: "/m/ruanyuyin/images/w_1.png",
                    name: "王者Ⅰ ",
                    rank_class: 'w_color',
                    reward: "送高级靓号或者4位靓号一个",
                    reward_class:'wangzhe'
                },
                {
                    icon: "/m/ruanyuyin/images/w_2.png",
                    name: "王者Ⅱ  ",
                    rank_class: 'w_color'
                },
                {
                    icon: "/m/ruanyuyin/images/w_3.png",
                    name: "王者Ⅲ  ",
                    rank_class: 'w_color'
                },
                {
                    icon: "/m/ruanyuyin/images/w_4.png",
                    name: "王者Ⅳ  ",
                    rank_class: 'w_color'
                },
                {
                    icon: "/m/ruanyuyin/images/w_5.png",
                    name: "王者Ⅴ  ",
                    rank_class: 'w_color'
                },
                {
                    icon: "/m/ruanyuyin/images/x_1.png",
                    name: "星耀Ⅰ ",
                    rank_class: 'x_color',
                    reward: "送4位高级靓号或者3位普通号一个",
                    reward_class:'xingyao'
                },
                {
                    icon: "/m/ruanyuyin/images/x_2.png",
                    name: "星耀Ⅱ  ",
                    rank_class: 'x_color'
                },
                {
                    icon: "/m/ruanyuyin/images/x_3.png",
                    name: "星耀Ⅲ  ",
                    rank_class: 'x_color'
                },
                {
                    icon: "/m/ruanyuyin/images/x_4.png",
                    name: "星耀Ⅳ  ",
                    rank_class: 'x_color'
                },
                {
                    icon: "/m/ruanyuyin/images/x_5.png",
                    name: "星耀Ⅴ  ",
                    rank_class: 'x_color'
                }
            ]
        },
        methods: {}
    };

    vm = XVue(opts);
</script>
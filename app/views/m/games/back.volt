{{ block_begin('head') }}
{{ theme_css('/m/css/main.css') }}
{{ theme_js('/js/vue.min.js') }}
{{ block_end() }}
<div id="app" class="select_game">
    <div class="select_game_instructions">
        <div class="instructions_title">
            <span class="wire"></span>
            <h3>说明</h3>
            <span class="wire"></span>
        </div>
        <p>1. 参与人数越多，胜利者奖励越高；未排名玩家消耗的游戏币总和，将按照比例分给胜利者</p>
        <p>2. 中途退出游戏的玩家不论分值均不计入排名</p>
    </div>
    {% if user_num %}
        <div class="current_game">
            <p>当前游戏模式：<span>${pay_type_text}模式</span></p>
            <p>奖金池共计：<span :class="pay_type == 'diamond'?'masonry':'gold'">${pay_type_text}奖金共{{ total_amount }}</span></p>
        </div>
        <div class="settlement_wire_box" >
            <span class="line"></span>
            <span class="point"></span>
            <span class="line"></span>
        </div>
        <ul class="settlement_box">
            <li v-for="user,index in users">
                <span :class="index>1?'text copper':(index==0?'text golden':'text silver')">${user.nickname}</span>
                <span :class="pay_type == 'diamond'?'masonry':'gold'">+${user.settlement_amount}</span>
            </li>
        </ul>
    {% endif %}
    <div class="settlement_back_btn">
        <span @click="go_back()">返回大厅</span>
    </div>
</div>
<script>
    var opts = {
        data: {
            pay_type_text: '',
            pay_type: "{{ pay_type }}",
            users: {{ users }},
            current_user_sid: "{{ current_user.sid }}",
            back_url:"{{ back_url }}"
        },
        methods: {
            go_back: function () {
                window.location.href = vm.back_url;
            }
        }
    };
    var vm = new XVue(opts);

    $(function () {
        switch (vm.pay_type) {
            case 'free':
                vm.pay_type_text = '免费';
                break;
            case 'gold':
                vm.pay_type_text = '金币';
                break;
            case 'diamond':
                vm.pay_type_text = '钻石';
                break;
        }
    });

</script>
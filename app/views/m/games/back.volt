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
        <p>1. 游戏发起人，可以设定游戏类型，参与需要支付等数量游戏币即可参与</p>
        <p>2. 参与游戏币模式游戏，最终胜利者将有意外收获哦～</p>
        <p>3. 未开始游戏，退出则不扣游戏币</p>
    </div>
    {% if user_num %}
    <div class="select_game_button">
        <p>当前游戏模式：<span>${pay_type_text}游戏，</span><span>${pay_type_text}奖金共{{ total_amount }}</span></p>
    </div>
    <ul class="settlement_box">
        <li v-for="user,index in users">
            <span :class="index>1?copper_class:(index==0?golden_class:silver_class)">第${index+1}名：</span>
            <span class="text">${user.nickname}</span>
            <span :class="pay_type == 'diamond'?diamond_class:gold_class">+${user.settlement_amount}</span>
        </li>
    </ul>
    {% endif %}
</div>
<script>
    var opts = {
        data: {
            selectGameType: 0,
            pay_type_text: '',
            pay_type: "{{ pay_type }}",
            users: {{ users }},
            rank_class: 'golden',
            ranking_text: '',
            golden_class:'golden',
            silver_class:'silver',
            copper_class:'copper',
            gold_class:'gold',
            diamond_class:'diamond'
        },
        methods: {}
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
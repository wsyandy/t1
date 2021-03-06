{{ block_begin('head') }}
{{ theme_css('/m/css/main.css') }}
{{ theme_js('/js/vue.min.js') }}
{{ block_end() }}
{% if current_user.id == game_host_user.id and can_create_game %}
    <div id="app" class="select_game">
        <div class="select_game_instructions">
            <div class="instructions_title">
                <span class="wire"></span>
                <h3>说明</h3>
                <span class="wire"></span>
            </div>
            <p>1. 房主可发起并设定游戏类型，房间里其他用户才可看到游戏入口，需支付等额游戏币即可参与</p>
            <p>2. 在限定时间内，不限次数跳一跳，以最后一次成绩作为结算</p>
            <p>3. 游戏未开始前，退出则不扣游戏币</p>
            <p>4. 游戏中，如果提前退出游戏，则扣除游戏币，且不计入排行榜</p>
            <p>5. 游戏币模式，结算将根据排名及玩家参与数获得不同奖励</p>
        </div>
        <ul class="select_game_select">
            <li>
                <span @click="selectgametype(0)" :class="{ 'radio_select': 0==select_game_type }" class="radio"></span>
                <span class="text">免费游戏</span>
            </li>
            <li>
                <span @click="selectgametype(1)" :class="{ 'radio_select': 1==select_game_type }" class="radio"></span>
                <span class="text">金币游戏</span>
                <input type="number" placeholder="请输入数目" v-model="gold_game_amount" class="gold"/>
                <span class="gold"></span>
            </li>
            <li v-if="current_user.diamond > 0">
                <span @click="selectgametype(2)" :class="{ 'radio_select': 2==select_game_type }" class="radio"
                ></span>
                <span class="text">钻石游戏</span>
                <input type="number" placeholder="请输入数目" v-model="diamond_game_amount" class="masonry"/>
                <span class="masonry"></span>
            </li>
        </ul>
        <div class="select_game_button">
            <p>当前游戏模式：<span>${ pay_type_text }游戏</span><span> ${amount}${pay_type_text}</span></p>
            <button @click="go_game()">发起游戏 GO</button>
        </div>
    </div>
    <script>
        var opts = {
            data: {
                select_game_type: 0,
                pay_type_text: '免费',
                diamond_game_amount: '',
                gold_game_amount: '',
                amount: 0,
                pay_type: 'free',
                game_host_user_id: "{{ game_host_user.id }}",
                current_user_id: "{{ current_user.id }}",
                sid: "{{ current_user.sid }}",
                game_id: "{{ game.id }}",
                current_user:{{ current_user }},
                code: '{{ code }}'
            },
            watch: {
                diamond_game_amount: function (val) {
                    vm.amount = val;

                    if (vm.select_game_type == 2) {

                    }
                },
                gold_game_amount: function (val) {
                    vm.amount = val;
                }
            },
            methods: {
                selectgametype: function (index) {
                    vm.select_game_type = index;
                    switch (index) {
                        case 0:
                            vm.pay_type = 'free';
                            vm.pay_type_text = '免费';
                            vm.amount = 0;
                            break;
                        case 1:
                            vm.pay_type = 'gold';
                            vm.pay_type_text = '金币';
                            vm.amount = vm.gold_game_amount;
                            break;
                        case 2:
                            vm.pay_type = 'diamond';
                            vm.pay_type_text = '钻石';
                            vm.amount = vm.diamond_game_amount;
                            break;
                    }
                },

                go_game: function () {
                    var data = {
                        user_id: vm.game_host_user_id,
                        pay_type: vm.pay_type,
                        amount: vm.amount,
                        game_id: vm.game_id,
                        sid: vm.sid,
                        code: vm.code
                    };

                    if (vm.pay_type == 'free' && vm.amount > 0) {
                        alert('选择游戏类型');
                        return;
                    }

                    $.authPost('/m/games/fee', data, function (resp) {
                        if (resp.error_code == 0) {
                            vm.redirectAction('/m/games/wait?game_history_id=' + resp.game_history_id + '&sid=' + vm.sid + '&code=' + vm.code);
                        } else {
                            alert(resp.error_reason);
                        }
                    });
                }
            }

        }
        var vm = new XVue(opts);
    </script>
{% else %}
    <div id="app" class="select_game">
        <div class="select_game_instructions">
            <div class="instructions_title">
                <span class="wire"></span>
                <h3>说明</h3>
                <span class="wire"></span>
            </div>
            <p>1. 房主和玩家均可发起并设定游戏类型，参与者需支付等额游戏币即可参与</p>
            <p>2. 在限定时间内，可以多次跳一跳，以最后一次成绩计入结算</p>
            <p>3. 未开始游戏，退出则不扣游戏币</p>
            <p>4. 如果提前退出本局游戏，则扣除游戏币，且不计入排行榜</p>
            <p>5. 游戏币模式，结算将根据排名及玩家参与数获得不同奖励</p>
        </div>
        {#这里是房主的游戏，显示其设定的入场费#}
        <div class="start_game">
            <span>${game_status_text}</span>
            <p></p>
            <p v-if="pay_type_text" :class="pay_type == 'diamond'?'masonry':'gold'">
                <span>${ pay_type_text }游戏</span><span> ${amount}${pay_type_text}</span>
            </p>
        </div>
        <div class="select_game_button">
            <button @click="go_game()">参与游戏 GO</button>
        </div>
        {#这里回头要加判断，判断用户用户的钻石数是否大于等于入场费#}
        <div class="prompt_toast" v-if="error_reason"><span>${error_reason}</span></div>
    </div>
    <script>
        var opts = {
            data: {
                pay_type: "{{ pay_type }}",
                pay_type_text: "",
                amount: "{{ amount }}",
                game_host_user_id: "{{ game_host_user.id }}",
                game_host_nickname: "{{ game_host_user.nickname }}",
                current_user_id: "{{ current_user.id }}",
                sid: "{{ current_user.sid }}",
                error_reason: '',
                game_status_text: '',
                current_user:{{ current_user }},
                can_create_game: "{{ can_create_game }}",
                game_history_id: "{{ game_history_id }}",
                game_id: "{{ game.id }}",
                code: "{{ code }}"
            },
            watch: {},
            methods: {
                go_game: function () {
                    if (!vm.pay_type) {
                        alert('暂无游戏发起者，请刷新！');
                        return;
                    }
                    var data = {
                        game_history_id: vm.game_history_id,
                        sid: vm.sid,
                        code: vm.code
                    };
                    $.authPost('/m/games/fee', data, function (resp) {
                        if (resp.error_code == 0) {
                            vm.redirectAction('/m/games/wait?game_history_id=' + resp.game_history_id + '&sid=' + vm.sid);
                        } else {
                            vm.error_reason = resp.error_reason;
                        }
                    });
                }
            }

        };

        $(function () {
            if (!vm.pay_type && vm.game_history_id) {
                if (vm.can_create_game) {
                    vm.game_status_text = vm.game_host_nickname + '正在发起游戏中，请稍后！';
                } else {
                    vm.game_status_text = '您不是主播,不能发起游戏';
                }
            } else {
                vm.game_status_text = vm.game_host_nickname + '已发起游戏';
            }

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
        var vm = new XVue(opts);
    </script>
{% endif %}

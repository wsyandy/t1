{{ block_begin('head') }}
{{ theme_css('/m/css/main.css') }}
{{ theme_js('/js/vue.min.js') }}
{{ block_end() }}
{% if current_user.id == room_host_id %}
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
        <ul class="select_game_select">
            <li>
                <span @click="selectgametype(0)" :class="{ 'radio_select': 0==selectGameType }" class="radio"></span>
                <span class="text">免费游戏</span>
            </li>
            <li>
                <span @click="selectgametype(1)" :class="{ 'radio_select': 1==selectGameType }" class="radio"></span>
                <span class="text">金币游戏</span>
                <input type="text" placeholder="请输入数目" v-model="gold_game_amount"/>
                <span class="gold"></span>
            </li>
            <li>
                <span @click="selectgametype(2)" :class="{ 'radio_select': 2==selectGameType }" class="radio"
                ></span>
                <span class="text">钻石游戏</span>
                <input type="text" placeholder="请输入数目" v-model="diamond_game_amount"/>
                <span class="masonry"></span>
            </li>
        </ul>
        <div class="select_game_button">
            <p>当前游戏模式：<span>${ game }游戏</span><span>费用为：${game_amount }${game}</span></p>
            <button @click="go_game()">参与游戏 GO</button>
        </div>
    </div>
    <script>
        var opts = {
            data: {
                selectGameType: 0,
                game: '免费',
                diamond_game_amount: '',
                gold_game_amount: '',
                game_amount: 0,
                game_type: 'free',
                room_host_id: "{{ current_user.id }}",
                sid: "{{ current_user.sid }}",
                code: 'yuewan'
            },
            watch: {
                diamond_game_amount: function (val) {
                    if (vm.selectGameType == 2) {
                        vm.game_amount = val;
                    }
                },
                gold_game_amount: function (val) {
                    if (vm.selectGameType == 1) {
                        vm.game_amount = val;
                    }
                }
            },
            methods: {
                selectgametype: function (index) {
                    vm.selectGameType = index;
                    switch (index) {
                        case 0:
                            vm.game_type = 'free';
                            vm.game = '免费';
                            vm.game_amount = 0;
                            break;
                        case 1:
                            vm.game_type = 'gold';
                            vm.game = '金币';
                            vm.game_amount = vm.gold_game_amount;
                            break;
                        case 2:
                            vm.game_type = 'diamond';
                            vm.game = '钻石';
                            vm.game_amount = vm.diamond_game_amount;
                            break;
                    }
                },

                go_game: function () {
                    var data = {
                        'user_id': vm.room_host_id,
                        'pay_type': vm.game_type,
                        'amount': vm.game_amount,
                        'code': vm.code,
                        'sid': "{{ current_user.sid }}"
                    };
                    $.authPost('/m/games/fee', data, function (resp) {
                        if (!resp.error_code) {
                            vm.redirectAction('/m/games/wait?code=' + vm.code + '&sid=' + vm.sid);
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
            <p>1. 游戏发起人，可以设定游戏类型，参与需要支付等数量游戏币即可参与</p>
            <p>2. 参与游戏币模式游戏，最终胜利者将有意外收获哦～</p>
            <p>3. 未开始游戏，退出则不扣游戏币</p>
        </div>
        {#这里是房主的游戏，显示其设定的入场费#}
        <div class="start_game">
            <span>发起者已设定</span>
            <p>${pay_type}游戏，${ pay_amount }${pay_type}</p>
        </div>
        <div class="select_game_button">
            <button @click="go_game()">参与游戏 GO</button>
        </div>
        {#这里回头要加判断，判断用户用户的钻石数是否大于等于入场费#}
        <div class="prompt_toast" v-if="can_game"><span>您当前的${error_reason}</span></div>
    </div>
    <script>
        var opts = {
            data: {
                pay_type: "",
                pay_amount: "{{ amount }}",
                game_user_id: "{{ current_user.id }}",
                can_game: false,
                error_reason: '钻石不足',
                sid:"{{ current_user.sid }}"
            },
            watch: {},
            methods: {
                go_game: function () {
                    if(!vm.pay_type){
                        alert('网络堵车，请刷新！！！');
                        return;
                    }
                    var data = {
                        'user_id': vm.game_user_id,
                        'pay_type': vm.pay_type,
                        'amount': vm.pay_amount,
                        'code': 'yuewan',
                        'sid': vm.sid
                    };
                    $.authPost('/m/games/fee', data, function (resp) {
                        if (!resp.error_code) {
                            vm.redirectAction('/m/games/wait?code=yuewan&sid=' + vm.sid);
                        } else {
                            vm.can_game = true;
                            vm.error_reason = resp.error_reason;
                        }
                    });
                }
            }

        };

        $(function () {
            var pay_type = "{{ pay_type }}";
            switch (pay_type) {
                case 'free':
                    vm.pay_type = '免费';
                    break;
                case 'gold':
                    vm.pay_type = '金币';
                    break;
                case 'diamond':
                    vm.pay_type = '钻石';
                    break;
            }
        });
        var vm = new XVue(opts);
    </script>
{% endif %}

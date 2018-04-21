{{ block_begin('head') }}
{{ theme_css('/m/css/main.css') }}
{{ theme_js('/js/vue.min.js') }}
{{ block_end() }}
{#用户对应的头像和昵称#}
<div id="app" class="select_game">
    <ul class="await_player_ul">
        <li v-for="user in users"><img :src="user.avatar_url" alt=""/><span>${user.nickname}</span>
            <span v-if="user.id == game_host_user_id" class="initiate">发起人</span>
        </li>
    </ul>
    {#这里要判断是否是房主，是由房主可以点击开始#}
    <div class="select_game_button">
        <button @click="start_game()" :class="current_user_id==game_host_user_id?'play':''">${ button_text }</button>
    </div>
    {% if current_user.id != game_host_user_id %}
        <div class="game_quit" style="opacity:0.5;">
            <div class="game_quit_button" @click="exit_game()">
                <span class="quit"></span>
                <span>退出</span>
            </div>
        </div>
    {% endif %}
</div>
<script>
    var interval_time;
    var opts = {
        data: {
            sid: "{{ current_user.sid }}",
            code: "{{ code }}",
            users: [],
            button_text: '开始',
            can_enter: 0,
            url: "{{ url }}",
            game_host_user_id: "{{ game_host_user_id }}",
            current_user_id: "{{ current_user.id }}",
            game_history_id: "{{ game_history_id }}"
        },
        watch: {},
        methods: {
            start_game: function () {
                if (vm.current_user_id == vm.game_host_user_id) {
                    var data = {
                        'code': vm.code,
                        'sid': vm.sid,
                        'game_history_id': vm.game_history_id
                    };
                    $.authPost('/m/games/start', data, function (resp) {
                        if (!resp.error_code) {
                            window.location.href = vm.url;
                        } else {
                            alert(resp.error_reason);
                        }
                    });
                } else {
                    alert('亲，请不要心急耐心等待一下。。。');
                }
            },
            exit_game: function () {
                var data = {
                    'code': vm.code,
                    'sid': vm.sid,
                    'game_history_id': vm.game_history_id
                };
                $.authPost('/m/games/exit', data, function (resp) {
                    if (!resp.error_code) {
                        window.location.href = document.referrer;
                    } else {
                        alert(resp.error_reason);
                    }
                });
            }
        }
    };
    var vm = new XVue(opts);

    function refreshUser() {

        var data = {
            'code': vm.code,
            'sid': vm.sid,
            'game_history_id': vm.game_history_id
        };

        $.authPost('/m/games/enter', data, function (resp) {
            if (resp.error_code == 0) {
                if (resp.can_enter == 1) {
                    clearInterval(interval_time);
                    window.location.href = vm.url;
                }else {
                    vm.users = resp.users;
                    vm.can_enter = resp.can_enter;
                }
            }else {
                if(resp.url){
                    clearInterval(interval_time);
                    alert(resp.error_reason);
                    window.location.href = resp.url;
                }
            }
        });
    }

    interval_time = setInterval(refreshUser, 1000);

    $(function () {
        if (vm.current_user_id != vm.game_host_user_id) {
            vm.button_text = '等待进入游戏，请稍后...';
        }

        refreshUser();
    });
</script>

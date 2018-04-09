{{ block_begin('head') }}
{{ theme_css('/m/css/main.css') }}
{{ theme_js('/js/vue.min.js') }}
{{ block_end() }}
<div id="app" class="select_game_list">
    <ul class="game_list_ul">
        <li v-for="game in game_list" @click="select_game(game.skip_url)">
            <img :src="game.icon_url" alt="">
            <span>${game.name}</span>
        </li>
    </ul>
</div>
<script>
    var opts = {
        data: {
            sid: "{{ sid }}",
            code: "{{ code }}",
            page: 1,
            per_page:8,
            total_page: 1,
            game_list: []
        },

        methods: {
            gameList: function () {

                if (vm.page > vm.total_page) {
                    return;
                }

                $.authGet('/m/games', {
                    page: vm.page,
                    per_page: vm.per_page,
                    sid: vm.sid,
                    code: vm.code,
                }, function (resp) {
                    vm.total_page = resp.total_page;
                    $.each(resp.games, function (index, game) {
                        vm.game_list.push(game);
                    })
                })

                vm.page++;
            },
            select_game: function (skip_url) {
                vm.redirectAction(skip_url + '?sid=' + vm.sid + '&code=' + vm.code);
            }

        }
    };
    vm = XVue(opts);

    $(function () {
        $(window).scroll(function () {
            if ($(document).scrollTop() >= $(document).height() - $(window).height()) {
                vm.gameList();
            }
        });
    })
    vm.gameList();
</script>
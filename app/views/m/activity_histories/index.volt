{{ block_begin('head') }}
{{ theme_css('/m/css/lucky_draw_activity.css') }}
{{ block_end() }}

<body style="background: #F3F3F3;">
<div class="list_box" id="app">
    <ul>
        <li v-for="activity_history in activity_histories">
            <h3><b>${activity_history.user_nickname}</b>获得<span>${activity_history.prize_type_text}</span></h3>
            <p>${activity_history.created_text}</p>
        </li>
    </ul>
</div>

<script>
    var opts = {
        data: {
            sid: '{{ sid }}',
            code: '{{ code }}',
            activity_id: '{{ activity_id }}',
            page: 1,
            total_page: 1,
            activity_histories: []
        },

        methods: {
            loadActivityHistories: function () {

                if (vm.page > vm.total_page) {
                    return;
                }

                $.authGet('/m/activity_histories', {
                    page: vm.page,
                    per_page: 20,
                    sid: vm.sid,
                    code: vm.code,
                    activity_id: vm.activity_id
                }, function (resp) {
                    vm.total_page = resp.total_page;
                    $.each(resp.activity_histories, function (index, activity_history) {
                        vm.activity_histories.push(activity_history);
                    })
                })

                vm.page++;
            }
        }
    };
    vm = XVue(opts);

    $(function () {
        $(window).scroll(function () {
            if ($(document).scrollTop() >= $(document).height() - $(window).height()) {
                vm.loadActivityHistories();
            }
        });
    })

    vm.loadActivityHistories();
</script>
</body>

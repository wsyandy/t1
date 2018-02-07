<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>领取记录</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/m/css/money_style.css">
    <script src="/js/vue/2.0.5/vue.min.js"></script>
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
    <script src="/js/utils.js"></script>
</head>
<body>
<div class="vuebox" id="app" v-cloak>

    <div class="get_top">累计领取：{{ total_money }}元</div>
    <div class="main_content">
        <div v-if="show">
                <ul class="get_details_ul" v-for="history in withdraw_histories">
                    <li>
                        <div class="top">
                            <p>金额</p>
                            <span>${ history.created_at_date }</span>
                        </div>
                        <div class="bottom">
                            <span>${ history.amount }</span>
                            <b>${ history.status_text }</b>
                        </div>
                    </li>
                </ul>
        </div>
        <div v-if="!show">
            <div class="get_none">
                <img src="/m/images/get_none.png">
                <p>天哪，您还没有领取记录！</p>
            </div>
        </div>
    </div>
</div>
<script>
    var opts = {
        data: {
            withdraw_histories: [],
            current_page: 1,
            total_page: 1,
            show: {{ flag }},
            sid: '{{ sid }}',
            code: '{{ code }}'

        },
        methods: {}
    };

    var vm = XVue(opts);
    console.log(vm.show);
    function getList() {
        if (vm.current_page > vm.total_page) {
            return;
        }
        data = {
            page: vm.current_page,
            per_page: 10,
            code: vm.code,
            sid: vm.sid
        };
        $.authGet('/m/withdraw_histories/list', data, function (resp) {
            vm.total_page = resp.total_page;
            $.each(resp.withdraw_histories, function (index, item) {
                vm.withdraw_histories.push(item);
            });
        });
        vm.current_page++;
    }

    getList();

    $(window).scroll(function () {
        var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if ($(document).height() - 20 <= totalheight) {
            getList();
        }
    });
</script>
</body>
</html>

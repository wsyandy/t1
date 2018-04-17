{{ block_begin('head') }}
{{ theme_css('/m/withdraw_histories/css/ruanyuyin_style.css') }}
{{ block_end() }}
<div class="vuebox" id="app" v-cloak>

    <div class="get_top" :hidden="show_total_money">累计领取：<span>{{ total_money }}元</span></div>
    <div class="main_content">
        <div v-show="withdraw_histories.length">
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
        <div v-if="!withdraw_histories.length">
            <div class="get_none">
                <img src="/m/withdraw_histories/images/get_none.png">
                <p>天哪，您还没有领取记录！</p>
            </div>
        </div>
    </div>
</div>
<script>
    var opts = {
        data: {
            show_total_money:true,
            total_money: "{{ total_money }}",
            withdraw_histories: [],
            current_page: 1,
            total_page: 1,
            sid: '{{ sid }}',
            code: '{{ code }}'

        },
        methods: {}
    };

    var vm = XVue(opts);

    function getList() {
        if(vm.total_money>0){
            vm.show_total_money = false;
        }
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
            console.log(resp);
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
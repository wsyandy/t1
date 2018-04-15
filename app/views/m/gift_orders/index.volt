{{ block_begin('head') }}
{{ theme_css('/m/css/gift_orders.css') }}
{{ block_end() }}

<div id="app" v-cloak>
    <div class="gift_nav" id="top">
        <p :class="{select_gift:receive}" @click.stop="receive=true">收到</p>
        <p :class="{select_gift:!receive}" @click.stop="receive=false">送出</p>
    </div>

    <div class="gift_money" v-if="receive"
         @click.stop="redirectAction('/m/withdraw_histories?sid={{ sid }}&code={{ code }}')">
        <h3>我的收益</h3>
        <p><span>{{ hi_coins }}</span> Hi币 <img src="/m/images/gift_icon.png"></p>
    </div>

    <div class="gift_list" v-for="gift_order in gift_orders" v-if="receive">
        <div class="list_left " @click.stop="userDetail(gift_order.sender_id)">
            <img :src="gift_order.sender_avatar_small_url">
        </div>
        <div class="list_right">
            <div class="top">
                <h3 @click.stop="userDetail(gift_order.sender_id)">${gift_order.sender_name} <span>送给您的礼物</span></h3>
                <p>${ gift_order.created_at_text }</p>
            </div>
            <div class="bottom">
                <div class="gift_pic">
                    <img :src="gift_order.image_small_url">
                </div>
                <div class="gift_num">
                    <p><span>礼物：</span> ${gift_order.name}x${ gift_order.gift_num }</p>
                    <p><span>价格：</span> <i :class="[gift_order.pay_type == 'diamond'? 'diamond':'gold']"></i> ${
                        gift_order.amount }${gift_order.pay_type_text}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="gift_list" v-for="gift_order in gift_orders" v-if="!receive">
        <div class="list_left" @click.stop="userDetail(gift_order.user_id)">
            <img :src="gift_order.user_avatar_small_url">
        </div>
        <div class="list_right">
            <div class="top">
                <h3 @click.stop="userDetail(gift_order.user_id)">${gift_order.user_name} <span>收到您的礼物</span></h3>
                <p>${ gift_order.created_at_text }</p>
            </div>
            <div class="bottom">
                <div class="gift_pic">
                    <img :src="gift_order.image_small_url">
                </div>
                <div class="gift_num">
                    <p><span>礼物：</span> ${gift_order.name}x${ gift_order.gift_num }</p>
                    <p><span>价格：</span> <i :class="[gift_order.pay_type == 'diamond'? 'diamond':'gold']"></i> ${
                        gift_order.amount }${gift_order.pay_type_text}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var opts = {
        data: {
            gift_orders: [],
            sid: '{{ sid }}',
            code: '{{ code }}',
            page: 1,
            per_page: 8,
            total_page: 1,
            loading: false,
            receive: true
        },
        watch: {
            receive: function () {
                vm.gift_orders = [];
                vm.page = 1;
                vm.total_page = 1;
                getGiftOrders();
            }
        },
        methods: {
            userDetail: function (user_id) {
                console.log(user_id);
                location.href = "app://users/other_detail?user_id=" + user_id;
            }
        }
    };

    var vm = XVue(opts);

    function getGiftOrders() {

        if (vm.page > vm.total_page) {
            return;
        }

        $params = {sid: vm.sid, code: vm.code, page: vm.page, per_page: vm.per_page};

        var url = '/m/gift_orders';

        if (!vm.receive) {
            var url = '/m/gift_orders/list';
        }

        $.get(url, $params, function (resp) {
            vm.total_page = resp.total_page;
            vm.loading = false;
            $.each(resp.gift_orders, function (index, gift_order) {
                vm.gift_orders.push(gift_order);
            })
        });

        vm.page++;
    }

    $(function () {

        window.onscroll = function () {
            var ht = document.documentElement.scrollTop || document.body.scrollTop;
            if (ht > 0) {
                $("#top").css({"position": "fixed", "top": "0px", "margin": "auto"});
            }
            else {
                $("#top").css({"position": "static"});
            }
        }


        getGiftOrders();

        $(window).scroll(function () {

            if ($(document).scrollTop() >= $(document).height() - $(window).height()) {

                if (vm.loading) {
                    return;
                }

                vm.loading = true;
                getGiftOrders();
            }
        });
    })

</script>
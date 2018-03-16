{{ block_begin('head') }}
    {{ weixin_css('mine_wallet.css') }}
{{ block_end() }}

<div class="haeder_nav">
    <span class="haeder_left_back" @click="backAction()"></span>
    <span>{{title}}</span>
    <span class="haeder_right_text"></span>
</div>
<div class="main_content" id="app" v-cloak>
    <div class="topup_select_money">

        {% if order is defined and order.isPaid() %}
            <span class="topup_results_successful"></span>
            <p class="topup_results_title">恭喜您，{{order.order_type_text}}成功</p>
        {% else %}
            <span class="topup_results_failure"></span>
            <p class="topup_results_title">很抱歉，{{order.order_type_text}}失败</p>
        {% endif %}
    </div>

</div>

<script>
    var opts = {
        data: {

        },
        methods: {
            backAction:function () {
                window.history.back();
            }

        }
    }
    var vm = XVue(opts);

</script>
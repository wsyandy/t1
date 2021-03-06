{{ block_begin('head') }}
{{ theme_css('/m/withdraw_histories/css/ruanyuyin_style.css') }}
{{ block_end() }}

<div id="app" v-cloak>
    <div class="balance_hi">
        <span class="balance_name">R币余额</span>
        <span class="balance_num">${hi_coins}</span>
    </div>

    <div class="exchange_title">
        <span>兑换</span>
    </div>

    <div class="exchange_box">
        {% for product in products %}
            <div class="exchange_list">

                <div class="exchange_diamond">
                    <img class="ico_diamond" src="/m/ruanyuyin/images/ruanyuyin_diamond.png" alt="">
                    <span>{{ product.getShowDiamond(user) }}</span>
                </div>

                {% if product.gold %}
                    <div class="exchange_gold">
                        <img class="ico_gold" src="/m/withdraw_histories/images/ico_gold.png" alt="">
                        <span>{{ product.gold }}</span>
                    </div>
                {% endif %}

                <div :class="['exchange_hi',product_id=={{ product.id }}?'cur':'']" data-target="select_province"
                     @click="choiceHiCoinAction({{ product.id }},'{{ product.hi_coins }}','{{ product.getShowDiamond(user) }}','{{ product.gold }}')">
                    <span>{{ product.hi_coins }}R币</span>
                </div>

            </div>
        {% endfor %}
    </div>

    <div class="exchange_footer">
        <img src="/m/withdraw_histories/images/warn.png" alt="" class="ico_tips"><span class="exchange_foot_text">温馨提示：至少20R币才能兑换钻石</span>
    </div>
    <div class="get_btn" @click="customHiCoinAction()">
        <a>自定义金额</a>
    </div>

    <div class="exchange_cover" v-show="is_pup">
        <div class="exchange_pupop">
            <div class="pupop_top">
                <h3>兑换钻石</h3>
                <p>R币余额：${ hi_coins }</p>
            </div>
            <div class="pupop_info" v-if="!is_custom" v-show="!no_hi_coin">
                <span class="equal_from">${cur_hi_coin}R币</span>
                <span class="equal_sign">=</span>
                <span class="equal_to">${cur_diamond} 钻石</span>
                <span class="equal_to" v-show="cur_gold > 0">+${cur_gold} 金币</span>
            </div>
            <div class="equal_tips" v-if="!is_custom" v-show="no_hi_coin">
                R币余额不足！
            </div>
            <div class="custom_box" v-if="is_custom">
                <input class="custom_input" placeholder="请输入兑换的" @focus="focusAction($event)"
                       v-on:input="customChangeAction"
                       v-model.number="cur_hi_coin" type="number" onkeyup="value=value.replace(/[^\d]/g,'')"
                       onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))">
                {#<div class="custom_equal">#}
                    <span class="custom_equal">R币 =</span>
                    {#<span></span>#}
                {#</div>#}

                {#<div class="custom_diamond">#}
                    <span class="custom_diamond" v-show="!isNaN(cur_diamond)">${cur_diamond}钻石</span>
                {#</div>#}

            </div>

            <div class="less_hi_coin" v-show="less_hi_coin">
                至少20R币才能兑换钻石
            </div>

            <div class="pupop_btn">
                <span class="btn_cancel" @click="cancelAction()">取消</span>
                <span :class="[no_hi_coin?'btn_sure':'btn_ensure']"
                      @click="exchangeDiamondsAction()">兑换</span>
            </div>
        </div>
    </div>
    <div class="get_success_money" v-show="is_tips">${no_hi_coin?'兑换成功！':'兑换失败！'}</div>
</div>

<script>

    var isTipsTimer;

    var opts = {
        data: {
            hi_coins: {{ user.getHiCoinText() }},
            sid: '{{ sid }}',
            code: '{{ code }}',
            hi_coin_diamond_rate:{{ hi_coin_diamond_rate }},
            product_id: null,
            cur_hi_coin: 0,
            cur_diamond: 0,
            cur_gold: 0,
            is_pup: false,
            is_tips: false,
            no_hi_coin: false,
            is_custom: false,
            less_hi_coin: false
        },
        methods: {
            choiceHiCoinAction: function (product_id, hi_coin, diamond, gold) {
                this.is_pup = true;
                this.product_id = product_id;
                this.cur_hi_coin = hi_coin;
                this.cur_diamond = diamond;
                this.cur_gold = gold;
                if (this.cur_hi_coin > this.hi_coins) {
                    this.no_hi_coin = true;
                }
                return;
            },
            customHiCoinAction: function () {
                this.is_pup = true;
                this.is_custom = true;
                this.no_hi_coin = true;
                return;
            },
            focusAction: function () {
                this.cur_hi_coin = '';
            },
            customChangeAction: function () {
                this.cur_diamond = this.cur_hi_coin * this.hi_coin_diamond_rate;
                this.no_hi_coin = this.cur_hi_coin < 20;
                this.less_hi_coin = this.cur_hi_coin < 20;
            },
            cancelAction: function () {
                this.product_id = null;
                this.cur_hi_coin = 0;
                this.is_pup = false;
                this.is_tips = false;
                this.cur_diamond = 0;
                this.cur_gold = 0;
                this.no_hi_coin = false;
                this.is_custom = false;
                this.less_hi_coin = false;
            },
            exchangeDiamondsAction: function () {

                clearTimeout(isTipsTimer);
                var post_data = {sid: vm.sid, code: vm.code, product_id: vm.product_id, hi_coins: this.cur_hi_coin};

                //自定义兑换不能小于20
                if (post_data.product_id == null && post_data.hi_coins < 20) {
                    this.less_hi_coin = true;
                    return;
                }

                $.post('/m/hi_coin_histories/create', post_data, function (resp) {

                    //兑换失败
                    if (resp.error_code != 0) {
                        vm.product_id = null;
                        vm.is_tips = true;
                        vm.no_hi_coin = false;
                        $('.tips_txt').html(resp.error_reason);
                    } else {
                        //兑换成功
                        vm.product_id = null;
                        vm.is_pup = false;
                        vm.is_tips = true;
                        vm.no_hi_coin = true;
                        vm.hi_coins = resp.hi_coins;
                    }

                    isTipsTimer = setTimeout(function () {
                        location.reload();
                    }, 1000);
                    return;

                });


            }
        }
    };
    var vm = XVue(opts);
</script>
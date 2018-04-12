{{ block_begin('head') }}
{{ theme_css('/m/withdraw_histories/css/apple', '/m/withdraw_histories/css/withdraw','/m/css/pop') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <div class="withdrawals_head">
        <div class="withdrawals_tips">总共可提现金额（元）</div>
        <div class="withdrawals_amount" v-text="amount "></div>
    </div>

    <ul class="withdrawals_box">
        <li class="withdrawals_list" @click="bankSelect">
            <span class="list_title">收款帐户</span>
            <input class="list_input" value="{{ withdraw_account.mobile }}" type="text" placeholder="请先输入银行卡号"
                   readonly="readonly">
            <img :src="arrow_right" class="arrow_right" alt="">
        </li>
        <li class="withdrawals_list">
            <span class="list_title">收款方式</span>
            <input class="list_input" value="{{ withdraw_account.type_text }}" type="text" placeholder="选择帐户后会识别方式"
                   readonly="readonly">

        </li>
        <li class="withdrawals_list">
            <span class="list_title">提取金额</span>
            <input class="list_input" v-model="bank_amount" type="number" placeholder="请输入1的整数倍金额" @input="bankAmount">
        </li>
    </ul>
    <a :class="['btn_disabled',{'btn_submit': !disabled}]" @click.stop="createWithdrawHistory"> 确认提现 </a>
    <div class="withdrawals_explain">
        <div class="explain_title">提现说明：</div>
        <ul class="explain_list">
            <li v-for="(item,index) in explain">${ index+1 }.${ item }</li>
        </ul>
    </div>
</div>

<script>

    var opts = {
        data: {
            arrow_right: "images/arrow_right.png",
            disabled: true,
            amount: {{ amount }},
            bank_number: '',
            bank_amount: '',
            can_withdraw: true,
            explain: ["1Hi币＝1人名币", "Hi币金额需大于或等于50元才可以提现。", "扶持期间提现无手续费，每周可提现一次，当周所提现的金额将在下周二到账。"]
        },
        created: function () {
        },
        methods: {
            bankSelect: function () {
                location.href = "/m/withdraw_accounts/index?sid=" + '{{ sid }}' + "&code=" + '{{ code }}';
            },

            bankAmount: function () {
                var amount = Number(this.amount);
                var curAmount = Number(this.bank_amount);
                if (curAmount <= amount) {
                    this.disabled = !curAmount
                } else {
                    this.bank_amount = this.amount
                }
            },
            createWithdrawHistory: function () {
                if (this.disabled) {
                    return false;
                }

                if (!this.can_withdraw) {
                    return false
                }

                var withdraw_account = {{ withdraw_account }};

                if (!withdraw_account) {
                    return false;
                }

                var data = {
                    sid: "{{ sid }}",
                    code: "{{ code }}",
                    withdraw_account_id: withdraw_account.id,
                    amount: this.bank_amount
                };

                console.log(data);
                this.can_withdraw = true;

                $.authPost("/m/withdraw_histories/create", data, function (resp) {
                    alert(resp.error_reason);
                    if (resp.error_code == 0) {
                        location.href = '/m/withdraw_histories/index?sid={{ sid }}&code={{ code }}';
                    }
                })
            }
        },
    };

    vm = XVue(opts);

    $(function() {
        pushHistory();
    });
    function pushHistory() {
        window.addEventListener("popstate", function(e) {
            alert("后退");
            self.location.reload();
        }, false);
        var state = {
            title : "",
            url : "#"
        };
        window.history.replaceState(state, "", "#");
    };
</script>
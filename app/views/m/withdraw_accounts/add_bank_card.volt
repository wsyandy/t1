{{ block_begin('head') }}
{{ theme_css('/m/withdraw_histories/css/apple', '/m/withdraw_accounts/css/add_bank_card') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <div class="bank_list">
        <span class="bank_card">银行卡</span>
        <input class="bank_input" type="number" v-model="account" type="text" placeholder="请先输入银行卡号" @input="bankInput"
               oninput="if(value.length>25)value=value.slice(0,25)">
        <img :src="ico_clear" v-show="isClear" alt="" class="ico_clear" @click="clearBankInput">
    </div>

    <a class="btn_submit" @click.stop="updateWithdrawAccount"> 提交 </a>
</div>

<script>
    var opts = {
        data: {
            ico_clear: "images/ico_clear.png",
            isClear: false,
            account: ''
        },
        created: function () {
        },
        methods: {
            bankInput: function () {
                this.isClear = true;
            },
            clearBankInput: function () {
                this.account = '';
                this.isClear = false;
            },
            updateWithdrawAccount: function () {
                var data = {
                    sid: '{{ sid }}',
                    code: '{{ code }}',
                    id: '{{ id }}',
                    account: this.account
                };

                $.authPost('/m/withdraw_accounts/update', data, function (resp) {
                    if (resp.error_code != 0) {
                        alert(resp.error_reason);
                    } else {
                        window.history.go(-2);
                    }
                });
            }
        }
    };

    vm = XVue(opts)
</script>


{{ block_begin('head') }}
{{ theme_css('/m/withdraw_histories/css/apple', '/m/withdraw_accounts/css/index') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <div class="card_head">
        <span class="card_title">银行卡</span>
        <span class="unbind" @click.stop="unbind" v-if="cardList.length">解除绑定</span>
    </div>
    <div class="card_list">
        <div class="card_add" @click="addCard" v-if="!cardList.length">
            <img :src="ico_add" class="ico_add" alt="">
            <span>添加储蓄卡</span>
        </div>
    </div>

    <ul class="card_list" v-if="cardList.length">
        <li v-for="item in cardList" @click.stop="selectWithdrawAccount(item)">
            <span>${ item.account_text }</span><span class="bind">绑定成功</span>
        </li>
    </ul>
</div>

<script>
    var opts = {
        data: {
            ico_add: "images/ico_add.png",
            selected_withdraw_account: '',
            cardList: {{ withdraw_accounts }}
        },
        created: function () {
        },
        methods: {
            addCard: function () {
                location.href = "/m/withdraw_accounts/add_mobile?sid=" + "{{ sid }}" + "&code=" + "{{ code }}";
            },
            selectWithdrawAccount: function (withdraw_account) {
                if (withdraw_account == this.selected_withdraw_account) {
                    return;
                }
                this.selected_withdraw_account = withdraw_account;
            },
            unbind: function () {
                var id = this.selected_withdraw_account.id ? this.selected_withdraw_account.id : this.cardList[0].id;

                location.href = "/m/withdraw_accounts/unbind?sid=" + "{{ sid }}" + "&code=" + "{{ code }}" + "&id=" + id;

            }
        }
    };

    vm = XVue(opts);

</script>
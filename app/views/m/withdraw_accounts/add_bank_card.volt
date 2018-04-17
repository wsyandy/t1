{{ block_begin('head') }}
{{ theme_css('/m/withdraw_histories/css/apple', '/m/withdraw_accounts/css/add_bank_card') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>

    <div class="bank_list">
        <span class="bank_card">银行卡</span>
        <input class="bank_input" type="text" v-model="account" type="text" placeholder="请先输入银行卡号"
               maxlength="25">
        <img :src="ico_clear" v-show="account" alt="" class="ico_clear" @click="clearBankInput(account)">
    </div>

    <div class="bank_list">
        <span class="bank_card">真实姓名</span>
        <input class="bank_input" type="text" v-model="user_name" type="text" placeholder="请先输入真实姓名"
               maxlength="10">
        <img :src="ico_clear" v-show="user_name" alt="" class="ico_clear"
             @click="clearUserNameInput()">
    </div>

    <div class="bank_list">
        <span class="bank_card">收款银行</span>
        <div class="select_area" @click="setSelect">
            <span>${options[selected].text}</span>
        </div>
    </div>

    <div class="bank_list">
        <span class="bank_card">收款银行支行</span>
        <input class="bank_input" type="text" v-model="bank_name" type="text" placeholder="请先输入真实姓名"
               maxlength="10">
        <img :src="ico_clear" v-show="bank_name" alt="" class="ico_clear"
             @click="clearBankNameInput()">
    </div>

    <a class="btn_submit" @click.stop="updateWithdrawAccount"> 提交 </a>

    <div :class="[isSet ? '' : 'fixed', 'popup_cover']">
        <div :class="[isSet ? '' : 'fixed', 'pop_bottom']">
            <ul>
                <li v-for="(option, index) in options" @click="setSelected(index)"> ${ option.text }</li>
            </ul>
            <div class="close_btn" @click="cancelSelect">取消</div>
        </div>
    </div>
</div>

<script>
    var opts = {
        data: {
            isPop: false,
            isSet: false,
            selected: 0,
            options: {{ banks }},
            ico_clear: "images/ico_clear.png",
            account: '',
            bank_id: 0,
            user_name: '',
            bank_name:'',
            can_submit: true
        },
        created: function () {
        },
        methods: {
            clearBankInput: function () {
                this.account = '';
            },
            clearUserNameInput: function () {
                this.user_name = '';
            },
            clearBankNameInput: function () {
                this.bank_name = '';
            },
            updateWithdrawAccount: function () {

                if (!this.can_submit) {
                    return;
                }

                if (!this.account) {
                    alert("请输入银行卡号");
                    return;
                }

                if (!this.bank_id) {
                    alert("请选择收款银行");
                    return;
                }

                if (!this.bank_name) {
                    alert("请输入收款银行支行");
                    return;
                }

                if (!this.user_name) {
                    alert("请输入真实姓名");
                    return;
                }

                var data = {
                    sid: '{{ sid }}',
                    code: '{{ code }}',
                    id: '{{ id }}',
                    account: this.account,
                    bank_id: this.bank_id,
                    user_name: this.user_name,
                    bank_name: this.bank_name
                };

                this.can_submit = false;

                $.authPost('/m/withdraw_accounts/update', data, function (resp) {
                    if (resp.error_code != 0) {
                        alert(resp.error_reason);
                    } else {
                        window.history.go(-2);
                    }
                    vm.can_submit = true;
                });
            },
            setSelect: function () {
                this.isSet = true
            },
            cancelSelect: function () {
                this.isSet = false
            },
            setSelected: function (index) {
                this.selected = index;
                this.isSet = false
            }
        }
    };

    vm = XVue(opts);

    $(function () {
        pushHistory();
    });

    //解决ios后退无法刷新
    function pushHistory() {
        window.addEventListener("popstate", function (e) {
            self.location.reload();
        }, false);
        var state = {
            title: "",
            url: "#"
        };
        window.history.replaceState(state, "", "#");
    }
</script>


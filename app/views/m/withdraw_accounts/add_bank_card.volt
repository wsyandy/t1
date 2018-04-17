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
        <span class="bank_card">收款户名</span>
        <input class="bank_input" type="text" v-model="user_name" type="text" placeholder="请先输入收款户名"
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
        <input class="bank_input" type="text" v-model="bank_account_location" type="text" placeholder="请先输入收款银行支行"
               maxlength="10">
        <img :src="ico_clear" v-show="bank_account_location" alt="" class="ico_clear"
             @click="clearBankNameInput()">
    </div>
    <div class="bank_list">
        <span class="bank_card">收款地区</span>
        <div class="select_area">
            <span @click="selectProvince" >${provinces[selected_province].text}</span>
            <span class="line-city">-</span>
            <span @click="selectCity">${cities[selected_city].text}</span>
        </div>
    </div>
    {#<div class="bank_list">#}
        {#<span class="bank_card">收款城市</span>#}
        {#<div class="select_area">#}

        {#</div>#}
    {#</div>#}

    <a class="btn_submit" @click.stop="updateWithdrawAccount"> 提交 </a>

    <div :class="[isSet ? '' : 'fixed', 'popup_cover']">
        <div :class="[isSet ? '' : 'fixed', 'pop_bottom']">
            <ul>
                <li v-for="(option, index) in options" @click="setSelected(index)"> ${ option.text }</li>
            </ul>
            <div class="close_btn" @click="cancelSelect">取消</div>
        </div>
    </div>

    <div :class="[isSetprovince ? '' : 'fixed', 'popup_cover']">
        <div :class="[isSetprovince ? '' : 'fixed', 'pop_bottom']">
            <ul>
                <li v-for="(province, index) in provinces" @click="setSelectedForProvince(index)"> ${ province.text }
                </li>
            </ul>
            <div class="close_btn" @click="cancelSelectForProvince">取消</div>
        </div>
    </div>

    <div :class="[isSetCity ? '' : 'fixed', 'popup_cover']">
        <div :class="[isSetCity ? '' : 'fixed', 'pop_bottom']">
            <ul>
                <li v-for="(city, index) in cities" @click="setSelectedForCity(index)"> ${ city.text }</li>
            </ul>
            <div class="close_btn" @click="cancelSelectForCity">取消</div>
        </div>
    </div>

</div>

<script>
    var opts = {
        data: {
            isPop: false,
            isSet: false,
            isSetprovince: false,
            isSetCity: false,
            selected: 0,
            selected_province: 0,
            selected_city: 0,
            options: {{ banks }},
            ico_clear: "images/ico_clear.png",
            account: '',
            account_bank_id: 0,
            province_id: 1,
            city_id: 1,
            user_name: '',
            bank_account_location: '',
            can_submit: true,
            provinces:{{ provinces }},
            cities: {{ city }}
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
                this.bank_account_location = '';
            },
            updateWithdrawAccount: function () {

                if (!this.can_submit) {
                    return;
                }

                if (!this.account) {
                    alert("请输入银行卡号");
                    return;
                }

                if (!this.account_bank_id) {
                    alert("请选择收款银行");
                    return;
                }

                if (!this.bank_account_location) {
                    alert("请输入收款银行支行");
                    return;
                }

                if (!this.user_name) {
                    alert("请输入收款户名");
                    return;
                }
                if (!this.province_id) {
                    alert("请选择收款省份");
                    return;
                }
                if (!this.city_id) {
                    alert("请选择收款城市");
                    return;
                }

                var data = {
                    sid: '{{ sid }}',
                    code: '{{ code }}',
                    id: '{{ id }}',
                    account: this.account,
                    account_bank_id: this.account_bank_id,
                    user_name: this.user_name,
                    bank_account_location: this.bank_account_location,
                    city_id:this.city_id,
                    province_id:this.province_id
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
                this.account_bank_id = this.options[this.selected].value;
                this.selected = index;
                this.isSet = false
            },

            selectProvince: function () {
                this.isSetprovince = true;
            },
            cancelSelectForProvince: function () {
                this.isSetprovince = false
            },
            setSelectedForProvince: function (index) {
                this.selected_province = index;
                this.province_id = this.provinces[this.selected_province].value;
                this.isSetprovince = false

                var data = {
                    sid: '{{ sid }}',
                    code: '{{ code }}',
                    province_id:  this.province_id,
                };

                $.authGet('/m/withdraw_accounts/get_cities', data, function (resp) {
                    if (resp.error_code != 0) {
                        alert(resp.error_reason);
                    } else {
                        vm.cities = resp.cities;
                    }
                });
            },
            selectCity: function () {
                this.isSetCity = true;
            },
            cancelSelectForCity: function () {
                this.isSetCity = false
            },
            setSelectedForCity: function (index) {
                this.selected_city = index;
                this.city_id = this.cities[this.selected_city].value;
                this.isSetCity = false
            }

        }
    };

    vm = XVue(opts);
</script>


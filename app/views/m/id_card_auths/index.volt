{{ block_begin('head') }}
{{ theme_css('/m/css/compere_auth', '/m/css/union_main') }}
{{ block_end() }}

<div class="vueBox" id="app">

    <div class="family-edit">
        <ul>
            <li>
                <span>真实姓名 </span>
                <div class="family_name" v-show="isEdit"> ${ user_info.id_name }</div>
                <input v-show="!isEdit" id="id_name" class="input_text" type="text"
                       placeholder="请输入您的真实姓名">
            </li>
            <li>
                <span>手机号码 </span>
                <div class="family_name" v-show="isEdit"> ${ user_info.moile }</div>
                <input v-show="!isEdit" id="mobile" class="input_text" type="text"
                       placeholder="请输入您的手机号码">
            </li>
            <li>
                <span>身份证号 </span>
                <div class="family_name" v-show="isEdit"> ${ user_info.id_no }</div>
                <input v-show="!isEdit" id="id_no" class="input_text" type="text" placeholder="请输入您的身份证号">
            </li>
            <li>
                <span>银行卡号 </span>
                <div class="family_name" v-show="isEdit"> ${ user_info.bank_account }</div>
                <input v-show="!isEdit" id="bank_account" class="input_text" type="text"
                       placeholder="请输入您的银行卡号">
            </li>


        </ul>
        <div class="agree_div" @click="agreeSelect">
            <img class="agree_img" :src="set_select"/>
            <div class="agree_text">
                <span class="agree_txt">阅读并同意</span>
                <span class="agree_txt">《主持认证协议》</span></div>
        </div>

        <div class="family-btn" :style="{backgroundColor: hasAgree?'#FDC8DA':'#F45189'}" @click="submit()">
            <span>确认提交 </span>
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
            receive: true,
            isEdit: '{{ current_user.id_card_auth == 1 or current_user.id_card_auth == 3 }}',
            user_info: {
                id_name: '{{ id_auth_auth ? id_auth_auth.id_name : '' }}',
                mobile: '{{ id_auth_auth ? id_auth_auth.mobile : '' }}',
                id_no: '{{ id_auth_auth ? id_auth_auth.id_no : '' }}',
                bank_account: '{{ id_auth_auth ? id_auth_auth.bank_account : '' }}'
            },
            hasAgree: true,
            set_select: '/m/images/ico-select.png',
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
            },
            agreeSelect: function () {
                if (this.hasAgree) {
                    this.hasAgree = false;
                    this.set_select = '/m/images/ico-selected.png';
                } else {
                    this.hasAgree = true;
                    this.set_select = '/m/images/ico-select.png';
                }
            },
            submit: function () {
                var id_name = $("#id_name").val();
                var id_no = $("#id_no").val();
                var mobile = $("#mobile").val();
                var bank_account = $("#bank_account").val();

                var params = {
                    id_name: id_name,
                    id_no: id_no,
                    mobile: mobile,
                    bank_account: bank_account,
                    sid: vm.sid,
                    code: vm.code,
                };

                $.authPost('/m/id_card_auths', params, function (resp) {
                    if (0 == resp.error_code) {
                        location.href = "/m/unions/index?sid={{ sid }}&code={{ code }}";
                    } else {
                        alert(resp.error_reason);
                    }
                })
            }
        }
    };

    var vm = XVue(opts);
</script>


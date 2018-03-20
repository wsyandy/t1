{{ block_begin('head') }}
{{ weixin_js('pay.js') }}
{{ block_end() }}

<div class="weixin_warn_box">
    <div class="wran">
        <i></i>
        <div id="error_reason">请填写正确的HI ID</div>
    </div>
</div>
<div class="weixin_chongzhi_top">
    <input required="required" id="user_id" name="user_id" type="text" class="name_input" value="{{ pay_user_id }}" placeholder="请输入您的Hi~ID"/>
    <i class="close_btn"></i>
    <p class="name">${nickname}</p>
</div>
<div class="weixin_title">选择充值金额</div>
<div class="weixin_cz_list">
    <ul>
        {% for product in products %}
            <li @click="rechargeAction({{ product.id }})">
                <div class="num">
                    <i></i>
                    <span>+{{ product.diamond }}</span>
                </div>
                <div class="money">￥{{ product.amount }}元</div>
            </li>
        {% endfor %}
    </ul>
</div>

<a class="faq_btn" href="/wx/payments/questions">
    <img class="ico_faq" src="/wx/{{ current_theme }}/images/ico_faq.png" alt="">
    <span>常见问题</span>
</a>

<script>


    $(function () {

        $('.close_btn').click(function () {
            $('.name_input').val('');
        });


        $('.name_input').focus(function () {
            $('.weixin_warn_box').fadeOut()
        });

        $('.weixin_cz_list ul li').each(function () {
            $(this).click(function () {
                $(this).children('.money').addClass('money_selected').parents().siblings().children('.money').removeClass('money_selected');
            })
        });

        $('.close_btn').click(function () {
            $('.name_input').val('');
        })

    });


    var opts = {
        data: {
            payment_channel_id: '{{ selected_payment_channel.id }}',
            payment_type: "{{ selected_payment_channel.payment_type }}",
            submit_status: false,
            nickname: "{{ pay_user_name }}"
        },
        methods: {
            rechargeAction: function (id) {

                if (this.submit_status) {
                    return false;
                }

                var user_id = $('#user_id').val();

                if (!user_id) {
                    smsTip('请填写正确的HI ID');
                    return;
                }

                var product_id = id;
                if (!product_id) {
                    smsTip('请选择钻石!');
                    return;
                }

                if (!this.payment_channel_id || !this.payment_type) {
                    smsTip('请选择支付渠道');
                    return;
                }

                var data = {
                    'user_id': user_id,
                    'payment_channel_id': this.payment_channel_id,
                    'payment_type': this.payment_type,
                    'product_id': product_id
                };

                this.submit_status = true;
                $.authPost('/wx/payments/create', data, function (resp) {
                    vm.submit_status = false;
                    vm.nickname = resp.nickname;
                    if (0 == resp.error_code) {
                        redirect_url = '/wx/payments/result?order_no=' + resp.order_no;
                        js_api_parameters = resp.form;
                        if ('weixin_js' == resp.payment_type) {//微信支付
                            wxPay();
                        } else {
                            alert(resp.payment_type);
                        }
                    } else {
                        smsTip(resp.error_reason);
                    }
                });

            }
        }
    };
    var vm = new XVue(opts);


    function smsTip(conetnt) {
        $("#error_reason").html(conetnt);
        $('.weixin_warn_box').fadeIn()
    }

</script>

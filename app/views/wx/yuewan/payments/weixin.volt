{{ block_begin('head') }}
{{ weixin_js('pay.js') }}
{{ block_end() }}

<div class="weixin_warn_box">
	<div class="wran">
		<i></i> 请填写正确的HI ID
	</div>
</div>
<div class="weixin_chongzhi_top">
	<input required="required" type="text" class="name_input" placeholder="请输入您的Hi~ID" />
	<i class="close_btn"></i>
	<input type="text" id="user_id" name="user_id" />
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

<script>

    var opts = {
        data: {
            payment_channel_id:{{ selected_payment_channel.id }},
            payment_type:"{{ selected_payment_channel.payment_type }}",
            submit_status:false
        },
        methods: {
            rechargeAction: function (id) {

                if(this.submit_status){
                    return false;
                }

                var product_id = id;
                if (!product_id) {
                    alert("请选择钻石!");
                    return;
                }

                if (!this.payment_channel_id || !this.payment_type) {
                    alert("请选择支付渠道!");
                    return;
                }

                var data = {
                    'payment_channel_id':this.payment_channel_id,
                    'payment_type':this.payment_type,
					'product_id':product_id
                };

                this.submit_status = true;
                $.authPost('/wx/payments/create',data, function(resp){
                    vm.submit_status = false;
                    if(0 == resp.error_code){
                        redirect_url = '/wx/payments/result?order_no=' + resp.order_no;
                        js_api_parameters = resp.form;
                        if ('weixin_js' == resp.payment_type){//微信支付
                            alert(resp.payment_type);
                            wxPay();
                        }
                    }else {
                        alert(resp.error_reason);
                    }
                });

            }
        }
    };
    var vm = new XVue(opts);

    $(function(){
        $('.weixin_cz_list ul li').each(function(){
            $(this).click(function(){
                $(this).children('.money').addClass('money_selected').parents().siblings().children('.money').removeClass('money_selected');
            })
        });

        $('.close_btn').click(function(){
            $('.name_input').val('');
        })

    });

</script>

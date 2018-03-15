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
			<li @click="submitAction({{ product.id }})">
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

        },
        methods: {
            submitAction: function (id) {
                console.log(id)
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

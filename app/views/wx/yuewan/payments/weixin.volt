<div class="weixin_warn_box">
	<div class="wran">
		<i></i> 请填写正确的HI ID
	</div>
</div>
<div class="weixin_chongzhi_top">
	<input required="required" type="text" class="name_input" placeholder="请输入您的Hi~ID" />
	<i class="close_btn"></i>
	<p class="name"> amy</p>
</div>
<div class="weixin_title">选择充值金额</div>
<div class="weixin_cz_list">
	<ul>
		<li>
			<div class="num">
				<i></i>
				<span>+60</span>
			</div>
			<div class="money">￥6元</div>
		</li>
		<li>
			<div class="num">
				<i></i>
				<span>+320</span>
			</div>
			<div class="money money_selected">￥6元</div>
		</li>
		<li>
			<div class="num">
				<i></i>
				<span>+750</span>
			</div>
			<div class="money">￥6元</div>
		</li>
		<li>
			<div class="num">
				<i></i>
				<span>+1320</span>
			</div>
			<div class="money">￥6元</div>
		</li>
		<li>
			<div class="num">
				<i></i>
				<span>+2280</span>
			</div>
			<div class="money">￥6元</div>
		</li>
		<li>
			<div class="num">
				<i></i>
				<span>+5750</span>
			</div>
			<div class="money">￥6元</div>
		</li>
		<li>
			<div class="num">
				<i></i>
				<span>+12280</span>
			</div>
			<div class="money">￥6元</div>
		</li>
	</ul>
</div>
<script type="text/javascript">
    $(function(){
        $('.weixin_cz_list ul li').each(function(){
            $(this).click(function(){
                $(this).children('.money').addClass('money_selected').parents().siblings().children('.money').removeClass('money_selected');
            })
        })

        $('.close_btn').click(function(){
            $('.name_input').val('');
        })

    })
</script>
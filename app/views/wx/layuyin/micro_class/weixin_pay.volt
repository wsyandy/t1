{{ block_begin('head') }}
{{ weixin_css('mine_wallet.css') }}
{{ block_end() }}
<div class="main_content">
    <div class="haeder_nav">
        <span class="haeder_left_back"></span>
        <span>充值</span>
        <span class="haeder_right_text">明细</span>
    </div>
    <div class="pay_course">
        <p>课程名称</p>
        <b>¥99</b>
    </div>
    <p class="pay_course_title">选择支付方式</p>
    <ul class="topup_way">
        <li>
            <span class="weixin">微信支付</span>
            <span class="select cur"></span>
        </li>
    </ul>
    <div class="topup_affirm">
        <span>占时无法购买</span>
    </div>
</div>
<script>
    $(function(){
        $('.topup_way li').click(function(){
            $(this).find('.select').addClass('cur')
            $(this).siblings().find('.select').removeClass('cur');
        })
    })
</script>
</body>
</html>

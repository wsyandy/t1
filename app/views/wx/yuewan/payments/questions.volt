<div  class="faq_title">
    <div class="q_text">
        <span class="">1. 如何更改并完善个人资料？</span>
        <div class="q_image"></div>
    </div>
    <div class="answer_text">
        <p>充值后可到APP-我的帐户中查看账户余额，此处冲的钻石实时到账</p>
    </div>
</div>
<div  class="faq_title">
    <div class="q_text">
        <span class="">2. 如何添加联系人？</span>
        <div class="q_image"></div>
    </div>
    <div class="answer_text">
        <p>非常抱歉，充错帐号是不能够办理退款的，您可以选择以下方式弥补损失：联系实际充值ID的主人，与对方协商是
            否愿意为此补偿您的充值。</p>

    </div>
</div>

<script>

    $(document).ready(function(){
        $(".q_text").click(function(){
            $(this).siblings(".answer_text").slideToggle("slow");
            var bool = $(this).children(".q_image").hasClass('up');
            if (bool){
                $(this).children(".q_image").removeClass('up');
            }else{
                $(this).children(".q_image").addClass('up');
            }
        });
    });
</script>


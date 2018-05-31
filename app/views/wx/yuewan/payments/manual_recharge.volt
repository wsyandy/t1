{{ block_begin('head') }}
    {{ weixin_css('manual_recharge.css') }}
{{ block_end() }}
<div class="transfer_tips">
    转账最低1万人民币起，购买的套餐和现有套餐的比例一样
</div>

<div class="transfer_info">
    <div class="transfer_title">  如需充值钻石，可以转账到以下账户进行充值：</div>
    <ul  class="transfer_list">
        <li>
            <div class="list_num"></div>
            <div class="list_text">
                <p class="list_info"><span>收款户名：</span><span class="copy_text">上海棕熊网络科技有限公司</span> </p>
                <p class="copy_btn"></p>
            </div>
        </li>
        <li> <div class="list_num"></div>
            <div class="list_text">
                <p class="list_info"><span>收款账号：</span><span class="copy_text">1001100409003083286</span> </p>
                <p class="copy_btn"></p>
            </div>
        </li>
        <li> <div class="list_num"></div>
            <div class="list_text">
                <p class="list_info"><span>收款银行：</span><span class="copy_text">中国工商银行股份有限公司上海市闵行支行</span></p>
                <p class="copy_btn"></p>
            </div>
        </li>

    </ul>
</div>
<div class="contact_box">
    <div class="contact">
        <div class="ico_warn"></div>
        <div class="contact_text">
            <span class="contact_title">请联系客服电话或者QQ进行充值</span>
            <div class="contact_tel">
                <span>客服电话：400-018-7755</span>
                <span>客服QQ：327041264</span>
            </div>
        </div>
    </div>
</div>

<div class="transfer_ps">
    PS：务必在转账附言里填写您的hi语音ID号，充值金额和联系方式

</div>

<div class="transfer_demo">
    <div class="transfer_demo_title">
        <span>示例</span>
    </div>
    <p>HI语音充值钻石 ID：2345，充值金额：10000，</p>
    <p>联系方式：13344564234</p>
</div>

<div class="copy_tips">
    <div class="copy_success">复制成功</div>
</div>

<script>

    $(function () {
        $('.copy_btn').on('click',function () {
            var text = $(this).siblings('.list_info').find('.copy_text').text();
            var oInput = document.createElement('input');
            oInput.value = text;
            oInput.disabled = true;
            document.body.appendChild(oInput);
            oInput.select(); // 选择对象
            document.execCommand("Copy"); // 执行浏览器复制命令
            oInput.className = 'oInput';
            oInput.style.display='none';
            $('.copy_tips').fadeIn().fadeOut()
        });
        $("#app").css("background-color","white");


    })

    var opts = {
        data: {

        },
        methods: {
            backAction:function () {
                window.history.back();
            }

        }
    }
    var vm = XVue(opts);

</script>
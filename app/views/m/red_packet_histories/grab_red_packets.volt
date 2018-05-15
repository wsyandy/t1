{{ block_begin('head') }}
{{ theme_css('/m/css/red_packet_address.css','/m/css/red_packet_index.css','/m/css/red_packet_sex_select.css') }}
{{ theme_js('/m/js/address.js','/m/js/font_rem.js') }}
{{ block_end() }}
<div class="get_hongbao_box" id="app">
    <div class="hongbao_box">
        <div class="wait_red wait_red_guanzhu">
            <div class="pic">
                <img src="">
            </div>
            <h4>{{ user_nickname }}</h4>
            <h3>发了一个红包</h3>

            {#<p>倒计时结束后可以抢</p>#}
            {#<div class="daojishi" id="time">#}

            {#<h3>发了一个红包，关注房主可领取</h3>#}
            {#<div class="qiang_red"></div>#}

            <div class="red_get">
                <img src="images/gongxi.png">
                <h3>抢到橘子发的钻石红包</h3>
                <div class="red_get_num"><i></i>100</div>
                <p>已收到我的帐户，可用于送礼物</p>
                <a href="javascript:;" class="look_detail">查看领取详情 <i></i></a>
            </div>



            </div>
        </div>
    </div>
</div>

<div class="guanzhu_qiang_box">
    <div class="gz_fangzhu">
        <i class="close"></i>
        <div class="pic">
            <img src="">
        </div>
        <h3>橘子</h3>
        <p>是否关注房主，领取红包</p>
        <div class="gz_btn">关注并领取</div>
    </div>
</div>
<script type="text/javascript">
    var opts={
        data: {
            sid: "{{ sid }}",
            code: "{{ code }}",
            red_packet_id: "{{ red_packet_id }}",
            red_packet_type: "{{ red_packet_type }}",


        },
        methods: {

        }
    }

    $(function(){
        var m=3;
        var s=0;
        setInterval(function(){
            if(s<10){
                //如果秒数少于10在前面加上0
                $('#time').html(m+':0'+s);
            }else{
                $('#time').html(m+':'+s);
            }
            s--;
            if(s<0){
                //如果秒数少于0就变成59秒
                s=59;
                m--;
            }
        },1000)
    });
    $(function(){
        $('.qiang_red').click(function(){
            $('.guanzhu_qiang_box').fadeIn(1000);
            setTimeout(function(){
                $('.gz_fangzhu').addClass('show');
            },10);

            $('.close').click(function(){
                $('.gz_fangzhu').removeClass('show');
                $('.guanzhu_qiang_box').fadeOut(1000);
            })
        })
    })
</script>
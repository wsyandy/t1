{{ block_begin('head') }}
{{ theme_css('/m/css/red_packet_address.css','/m/css/red_packet_index.css','/m/css/red_packet_sex_select.css') }}
{{ theme_js('/m/js/font_rem.js') }}
{{ block_end() }}
<div id="app">
    <div class="get_hongbao_box" >
        <div class="hongbao_box">
        <div class="wait_red wait_red_guanzhu">
            <div id="hide">
                <div class="pic">
                    <img src="{{ user_avatar_url }}">
                </div>
                <h4>{{ user_nickname }}</h4>
                <h3>发了一个红包</h3>

                <div id="start_time">
                    <p>倒计时结束后可以抢</p>
                    <div class="daojishi" id="time"></div>
                </div>
                <div id="end_time">
                    <p v-if="red_packet_type == 'attention'">发了一个红包，关注房主可领取</p>
                    <div class="qiang_red" @click="getRedPacket"></div>
                </div>


            </div>
                {#<h3>发了一个红包，关注房主可领取</h3>#}
                {#<div class="qiang_red"></div>#}

                {#<div class="red_get">#}
                    {#<img src="images/gongxi.png">#}
                    {#<h3>抢到橘子发的钻石红包</h3>#}
                    {#<div class="red_get_num"><i></i>100</div>#}
                    {#<p>已收到我的帐户，可用于送礼物</p>#}
                    {#<a href="javascript:;" class="look_detail">查看领取详情 <i></i></a>#}
                {#</div>#}
        <div v-if="getRed">
            <div class="get_hongbao_box">
                <div class="hongbao_box">
                    <div class="red_get" v-if="congratulation">
                        <img src="/m/images/gongxi.png">
                        <h3>${res}</h3>
                        <div class="red_get_num"><i></i>${getDiamond}</div>
                        <p>已收到我的帐户，可用于送礼物</p>
                        <a @click="toDetail()" class="look_detail">查看领取详情 <i></i></a>
                    </div>
                    <div class="red_over" v-if="pity">
                        <img src="/m/images/yihan.png">
                        <h3>${res}</h3>
                        <a @click="toDetail()" class="look_detail">查看领取详情 <i></i></a>
                    </div>
                </div>
            </div>
        </div>

            </div>
        </div>
    </div>
</div>

<div class="guanzhu_qiang_box" v-if="attentionHost">
    <div class="gz_fangzhu">
        <i class="close"></i>
        <div class="pic">
            <img src="{{ user_avatar_url }}">
        </div>
        <h3>{{ user_nickname }}</h3>
        <p>是否关注房主，领取红包</p>
        <div class="gz_btn" @click="toAttention()">关注并领取</div>
    </div>
</div>
<script type="text/javascript">
    var opts = {
        data: {
            sid: "{{ sid }}",
            code: "{{ code }}",
            red_packet_id: "{{ red_packet.id }}",
            red_packet_type: "{{ red_packet.red_packet_type }}",
            getRed:false,
            congratulation:false,
            pity:false,
            attentionHost:false,
            attentionUrl:"",
            res:"",
            getDiamond:"",

        },
        methods: {
            getRedPacket: function () {
                var data = {
                    sid: this.sid,
                    code: this.code,
                    red_packet_id: vm.red_packet_id,
                    red_packet_type: vm.red_packet_type,
                }


                $.authGet('/m/red_packet_histories/grab_red_packets', data, function (resp) {
                    //console.log(resp);
                    vm.getRed = true;
                    if(!resp.error_code) {

                        vm.res = resp.error_reason;
                        vm.getDiamond = resp.get_diamond;
                        vm.congratulation = true;

                    }else if(resp.error_code == -400){

                        vm.attentionHost = true;
                        vm.attentionUrl = resp.error_url;

                    }else{

                        vm.res = resp.error_reason;
                        vm.pity = true;

                    }
                    hide_grab();


                });
            },
            toDetail: function () {
                var url = "/m/red_packet_histories/detail?sid="+this.sid+"&code="+this.code+"&red_packet_id="+this.red_packet_id;

                location.href = url;
            },
            toAttention: function () {
                var data = {
                    sid: this.sid,
                    code: this.code,
                    red_packet_id:vm.red_packet_id,
                }
                $.authGet(vm.attentionUrl, data, function (resp) {
                    console.log(resp);
                })
            }
        }
    }

    vm = XVue(opts);

    $(function () {
        var t = {{ distance_start_at }};
        var m = parseInt(t/60);
        var s = t%60;
        if( t > 0 ){
            $("#end_time").hide();
            setInterval(function () {
                if (s < 10) {
                    //如果秒数少于10在前面加上0
                    $('#time').html(m + ':0' + s);
                } else {
                    $('#time').html(m + ':' + s);
                }
                s--;
                if (s < 0 ) {
                    //如果秒数少于0就变成59秒
                    s = 59;
                    m--;
                }
                if(m <= 0 && s <= 0){
                    $("#end_time").show();
                    $("#start_time").hide();
                    clearInterval();
                }
            }, 1000);
        }else{
            $("#end_time").show();
            $("#start_time").hide();
        }


    });

    function hide_grab(){
        $("#hide").hide();

    }
//    $(function () {
//        $('.qiang_red').click(function () {
//            $('.guanzhu_qiang_box').fadeIn(1000);
//            setTimeout(function () {
//                $('.gz_fangzhu').addClass('show');
//            }, 10);
//
//            $('.close').click(function () {
//                $('.gz_fangzhu').removeClass('show');
//                $('.guanzhu_qiang_box').fadeOut(1000);
//            })
//        })
//    })
</script>
{{ block_begin('head') }}
{{ theme_css('/m/css/red_packet_address.css','/m/css/red_packet_index.css','/m/css/red_packet_sex_select.css') }}
{{ theme_js('/m/js/address.js','/m/js/font_rem.js') }}
{{ block_end() }}
<div class="detail_list red_list">
    <ul>
        <li>
            <div class="pic">
                <img src="">
            </div>
            <div class="list_text">
                <div class="name">
                    <h3>橙子的颜色</h3>
                    <p>发了一个红包</p>
                </div>
                <div class="num red_list_style red_list_qiang">抢</div>
            </div>
        </li>
        <li>
            <div class="pic">
                <img src="">
            </div>
            <div class="list_text">
                <div class="name">
                    <h3>橙子的颜色</h3>
                    <p>发了一个红包</p>
                </div>
                <div class="num red_list_style red_list_time" id="time"></div>
            </div>
        </li>
        <li>
            <div class="pic">
                <img src="">
            </div>
            <div class="list_text">
                <div class="name">
                    <h3>橙子的颜色</h3>
                    <p>发了一个红包</p>
                </div>
                <div class="num red_list_style red_list_get_red">已抢过</div>
            </div>
        </li>
        <li>
            <div class="pic">
                <img src="">
            </div>
            <div class="list_text">
                <div class="name">
                    <h3>橙子的颜色</h3>
                    <p>发了一个红包</p>
                </div>
                <div class="num red_list_style red_list_fangzhu">关注房主可抢</div>
            </div>
        </li>
    </ul>
</div>
<script type="text/javascript">
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
    })
</script>
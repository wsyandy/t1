{{ block_begin('head') }}
{{ theme_css('/m/css/red_packet_address.css','/m/css/red_packet_index.css','/m/css/red_packet_sex_select.css') }}
{{ theme_js('/m/js/address.js','/m/js/font_rem.js') }}
{{ block_end() }}
<div class="detail_list red_list" id="app">
    <ul>
        <li v-for="v,i in red_packets_list" href="/m/red_packets_histories/grab_red_packets?red_packet_id=${id}&red_packet_type=${red_packet_type}&sex=${sex}">
            <div class="pic">
                <img :src="v.user_avatar_url">
            </div>
            <div class="list_text">
                <div class="name">
                    <h3>${v.user_nickanme}</h3>
                    <p>发了一个红包</p>
                </div>
                <div class="num red_list_style red_list_qiang">抢</div>
            </div>
        </li>
        {#<li>#}
            {#<div class="pic">#}
                {#<img src="">#}
            {#</div>#}
            {#<div class="list_text">#}
                {#<div class="name">#}
                    {#<h3>橙子的颜色</h3>#}
                    {#<p>发了一个红包</p>#}
                {#</div>#}
                {#<div class="num red_list_style red_list_time" id="time"></div>#}
            {#</div>#}
        {#</li>#}
        {#<li>#}
            {#<div class="pic">#}
                {#<img src="">#}
            {#</div>#}
            {#<div class="list_text">#}
                {#<div class="name">#}
                    {#<h3>橙子的颜色</h3>#}
                    {#<p>发了一个红包</p>#}
                {#</div>#}
                {#<div class="num red_list_style red_list_get_red">已抢过</div>#}
            {#</div>#}
        {#</li>#}
        {#<li>#}
            {#<div class="pic">#}
                {#<img src="">#}
            {#</div>#}
            {#<div class="list_text">#}
                {#<div class="name">#}
                    {#<h3>橙子的颜色</h3>#}
                    {#<p>发了一个红包</p>#}
                {#</div>#}
                {#<div class="num red_list_style red_list_fangzhu">关注房主可抢</div>#}
            {#</div>#}
        {#</li>#}
    </ul>
</div>
<script>
    var opts = {
        data: {
            sid: "{{ sid }}",
            code: "{{ code }}",
            page: 1,
            per_page:3,
            total_page: 1,
            red_packets_list: [],
            room_id: "{{ room_id }}",
        },

        methods: {
            RedPacketsList: function () {

                if (vm.page > vm.total_page) {
                    return;
                }
                var data = {
                    page: vm.page,
                    per_page: vm.per_page,
                    sid: vm.sid,
                    code: vm.code,
                    room_id:172,

                }
//console.log(data);
                $.authGet('/m/red_packet_histories/red_packets_list', data , function (resp) {
                    console.log(resp);
                    vm.total_page = resp.total_page;
                    $.each(resp.red_packets, function (index, val) {
                        vm.red_packets_list.push(val);
                    })
                    console.log(vm.red_packets_list);
                })

                vm.page++;
            }
//            select_game: function (game) {
//                if(!game.url){
//                    alert('url无效');
//                    return;
//                }
//                vm.redirectAction(game.url + '?sid=' + vm.sid + '&code=' + vm.code + '&game_id=' + game.id);
//            }

        }
    };
    vm = XVue(opts);

    $(function () {
        $(window).scroll(function () {
            if ($(document).scrollTop() >= $(document).height() - $(window).height()) {
                vm.RedPacketsList();
            }
        });
    })
    vm.RedPacketsList();

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
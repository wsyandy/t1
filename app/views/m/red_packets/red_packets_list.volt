{{ block_begin('head') }}
{{ theme_css('/m/css/red_packet_address.css','/m/css/red_packet_index.css','/m/css/red_packet_sex_select.css') }}
{{ theme_js('/m/js/address.js','/m/js/font_rem.js') }}
{{ block_end() }}
<div class="detail_list red_list" id="app">
    <ul>
        <li v-for="v,i in red_packets_list">
            <div class="pic">
                <img :src="v.user_avatar_url">
            </div>
            <div class="list_text">
                <div class="name">
                    <h3>${v.user_nickname}</h3>
                    <p>发了一个红包</p>
                </div>
                <div class="num red_list_style red_list_get_red" v-if="v.is_grabbed">已抢过</div>

                <div class="num red_list_style" v-bind:class="v.class" v-if="v.is_grab"
                     @click="toGrabRedPacket(v.id,v.red_packet_type)">${v.text}
                </div>

                <div class="num red_list_style red_list_time " v-if="v.is_stay" :class="['daojishi'+i]"
                     @click="toGrabRedPacket(v.id,v.red_packet_type)" ></div>
                <div class="num red_list_style red_list_qiang " :class="['daojishiJS'+i]" style="display: none;"
                     v-if="v.red_packet_type == 'stay_at_room'" @click="toGrabRedPacket(v.id,v.red_packet_type)">抢
                    </div>
            </div>
        </li>
    </ul>
</div>
<script>
    var opts = {
        data: {
            sid: "{{ sid }}",
            code: "{{ code }}",
            page: 1,
            per_page: 3,
            total_page: 1,
            red_packets_list: [],
            room_id: "{{ room_id }}",
            user_get_red_packet_ids: [],
        },
        mounted:function () {


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
                    room_id: vm.room_id
                };

                $.authGet('/m/red_packets/red_packets_list', data, function (resp) {
                    console.log(resp);
                    vm.total_page = resp.total_page;
                    vm.user_get_red_packet_ids = resp.user_get_red_packet_ids;
                    $.each(resp.red_packets, function (i, val) {

                        var index = $.inArray(val.id, vm.user_get_red_packet_ids);
                        if (index != -1) {
                            val.is_grabbed = true;
                        } else {
                            val.is_grab = true;
                            val.text = "抢";
                            val.class = "red_list_qiang";
                        }

                        if (val.red_packet_type == 'follow') {
                            val.text = "关注可抢";
                            val.class = "red_list_fangzhu";
                        }
                        if (val.red_packet_type == 'stay_at_room' && val.distance_start_at > 0) {
                            val.is_stay = true;
                            val.is_grab = false;
                            val.is_grabbed = false;
                            val.is_stay_show = false;

                            distanceStartAt(val.distance_start_at,i)
                        }


//

                        vm.red_packets_list.push(val);
                    });


                });


                vm.page++;
            },
            toGrabRedPacket: function (id, type) {
                var url = "/m/red_packets/grab_red_packets?sid=" + this.sid + "&code=" + this.code + "&red_packet_id=" + id + "&red_packet_type=" + type;

                location.href = url;
            }
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


    function distanceStartAt(t,i) {

        var ss;
        var m = parseInt(t / 60);
        var s = t % 60;
        var clasName = '.daojishi'+i;
        var clajsName = '.daojishiJS'+i;
        if (t > 0) {
            //$("#end_time").hide();
            setInterval(function () {
                if (s < 10) {
                    //如果秒数少于10在前面加上0

                    ss= m + ':0' + s;
                } else {
                    ss= m + ':' + s;
                }
                s--;
                if (s < 0) {
                    //如果秒数少于0就变成59秒
                    s = 59;
                    m--;
                }

                if (m <= 0 && s <= 0) {
                    $(clajsName).show();
                    $(clasName).hide();
                }

                $(clasName).html(ss);

            }, 1000);
        }else{
            $(clajsName).show();
            $(clasName).hide();
        }

    }


</script>
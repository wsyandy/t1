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

                {#<div class="num red_list_style red_list_get_red" v-if="v.is_grabbed">附近的人可抢</div>#}
                <div class="num red_list_style" v-bind:class="v.class" v-if="v.is_grab"
                     @click="toGrabRedPacket(v.id,v.red_packet_type)">${v.text}
                </div>

                <div class="num red_list_style red_list_time daojishi" v-if="v.is_stay" @click="toGrabRedPacket(v.id,v.red_packet_type)"></div>
                <div class="num red_list_style red_list_qiang daojishiJS" style="display: none;"
                     v-if="v.red_packet_type == 'stay_at_room'" @click="toGrabRedPacket(v.id,v.red_packet_type)">抢
                    {#</div>#}
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
            distance_start_at: "",
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
                    vm.distance_start_at = resp.distance_start_at;
                    $.each(resp.red_packets, function (index, val) {
                        var index = $.inArray(val.id, vm.user_get_red_packet_ids);
                        if (index != -1) {
                            val.is_grabbed = true;
                        } else {
                            val.is_grab = true;
                            val.text = "抢";
                            val.class = "red_list_qiang";
                        }

                        if (val.red_packet_type == 'nearby') {
                            val.text = "附近人可抢";
                            val.class = "red_list_fangzhu";
                        }
                        if (val.red_packet_type == 'attention') {
                            val.text = "关注可抢";
                            val.class = "red_list_fangzhu";
                        }
                        if (val.red_packet_type == 'stay_at_room') {
                            val.is_stay = true;
                            val.is_grab = false;
                            val.is_grabbed = false;
                            val.is_stay_show = false;
                        }
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

    setTimeout(function () {
        distanceStartAt();
    }, 500);

    function distanceStartAt() {
        var t = vm.distance_start_at;
        console.log(t);
        var m = parseInt(t / 60);
        var s = t % 60;
        if (t > 0) {
            //$("#end_time").hide();
            setInterval(function () {
                if (s < 10) {
                    //如果秒数少于10在前面加上0
                    $('.daojishi').html(m + ':0' + s);
                } else {
                    $('.daojishi').html(m + ':' + s);
                }
                s--;
                if (s < 0) {
                    //如果秒数少于0就变成59秒
                    s = 59;
                    m--;
                }
                if (m <= 0 && s <= 0) {

                    $(".daojishiJS").show();
                    $(".daojishi").hide();


                }
            }, 1000);
        }

    }


</script>
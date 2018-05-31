{{ block_begin('head') }}
{{ theme_css('/m/css/red_packet_address.css','/m/css/red_packet_index.css','/m/css/red_packet_sex_select.css') }}
{{ theme_js('/m/js/font_rem.js') }}
{{ block_end() }}
<div id="app" v-cloak class="grab">
    <div class="detail_red_top">
        <div class="top_person">
            <div class="pic">
                <img src="{{ red_packet['user_avatar_url'] }}">
            </div>
            <h3>共{{ red_packet['diamond'] }}钻</h3>
            <p>来自{{ red_packet['user_nickname'] }}的红包</p>
        </div>
    </div>
    <div class="detail_get">
        <span>已领取{{red_packet['num']- red_packet['balance_num']  }}/{{ red_packet['num'] }}</span>
        <span>共{{red_packet['diamond']- red_packet['balance_diamond']}}／{{ red_packet['diamond'] }}钻</span>
    </div>
    <div class="detail_list">
        <ul>
            <li v-for="v in getRedPacketUsers">
                <div class="pic">
                    <img :src="v.avatar_url">
                </div>
                <div class="list_text">
                    <div class="name">
                        <h3>${v.nickname}</h3>
                        <p>${v.get_diamond_at}</p>
                    </div>
                    <div class="num">${v.get_diamond ? v.get_diamond : 0 }<i></i></div>
                </div>
            </li>
        </ul>
    </div>
</div>

<script>
    var opts = {
        data: {
            sid: "{{ sid }}",
            code:"{{ code }}",
            getRedPacketUsers:[],
            red_packet_id:"{{red_packet['id']}}",
            room_id:"{{ red_packet['current_room_id'] }}"
        },

        created:function() {
            var data = {
                sid:this.sid,
                code:this.code,
                red_packet_id:this.red_packet_id,
                room_id:this.room_id
            }
            $.authGet('/m/red_packets/get_red_packet_users', data, function (resp) {
                console.log(resp);
                if(!resp.error_code){
                    vm.getRedPacketUsers = resp.get_red_packet_users;
                }

            });
        }

    }

    vm = XVue(opts);
</script>
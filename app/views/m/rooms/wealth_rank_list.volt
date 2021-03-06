{{ block_begin('head') }}
{{ theme_css('/m/css/wealth_list_rank.css') }}
{{ theme_js('/js/font_rem.js') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak="">
    <div class="bangdan_box">
        <div class="bangdan_title">
            <ul>
                <li :class="{'li_selected':cur_idx===index}" v-for="(item,index) in ranking_tab"
                    @click='rankingSelect(index)'>
                    ${item}
                </li>
            </ul>
        </div>

        <div class="one" v-if="users.length" @click.stop="userDetail(users[0].id)">
            <div class="one_pic">
                <div class="pic">
                    <img :src="users[0].avatar_small_url">
                </div>
                <img src="/m/images/one_num.png" class="one_num">
            </div>
            <h3>${ users[0].nickname }
                <img :src="users[0].level_img" v-if="users[0].level">
                <span :class="users[0].sex ? 'men' :'women'"
                      v-text="users[0].age?users[0].age:''"
                      :style="users[0].age? 'background-position:0.16rem' : 'background-position:center'"></span></h3>
            <p v-show="is_host"><span>${ users[0].wealth_value }</span>贡献</p>
        </div>
        <table class="table">
            <tr v-if="users.length >= 2" @click.stop="userDetail(users[1].id)">
                <td style="width:12%;">
                    <img class="voice_ico" src="/m/images/room_weath_two.png" alt="">
                </td>
                <td style="width:24%;">
                    <div class="two_pic">
                        <div class="pic">
                            <img :src="users[1].avatar_small_url">
                        </div>
                        <img src="/m/images/two_num.png" class="two_num">
                    </div>
                </td>
                <td>
                    <h5><span class="two_color">${ users[1].nickname }</span>
                        <img :src="users[1].level_img" v-if="users[1].level">
                        <i :class="users[1].sex ? 'men' :'women'"
                           v-text="users[1].age?users[1].age:''"
                           :style="users[1].age? 'background-position:0.16rem' : 'background-position:center'"></i>
                    </h5>
                    <p v-show="is_host">${ users[1].wealth_value }贡献</p>
                </td>
            </tr>
            <tr v-if="users.length >= 3" @click.stop="userDetail(users[2].id)">
                <td style="width:12%;">
                    <img class="voice_ico" src="/m/images/room_wealth_three.png" alt="">
                </td>
                <td style="width:24%;">
                    <div class="two_pic three_pic">
                        <div class="pic">
                            <img :src="users[2].avatar_small_url">
                        </div>
                        <img src="/m/images/three_num.png" class="two_num">
                    </div>
                </td>
                <td>
                    <h5><span class="three_color">${ users[2].nickname }</span>
                        <img :src="users[2].level_img" v-if="users[2].level">
                        <i :class="users[2].sex ? 'men' :'women'"
                           v-text="users[2].age?users[2].age:''"
                           :style="users[2].age? 'background-position:0.16rem' : 'background-position:center'"></i></h5>
                    <p v-show="is_host">${ users[2].wealth_value }贡献</p>
                </td>
            </tr>

        </table>
        <div class="line"></div>
        <table class="table table_last">
            <tr v-for="(user,index) in users.slice(3)" @click.stop="userDetail(user.id)">
                <td style="width:12%;" v-text="index+4"></td>
                <td style="width:24%;">
                    <div class="pic">
                        <img :src="user.avatar_small_url">
                    </div>
                </td>
                <td>
                    <h5>${ user.nickname }
                        <img :src="user.level_img" v-if="user.level">
                        <i :class="user.sex ? 'men' :'women'"
                           v-text="user.age?user.age:''"
                           :style="user.age? 'background-position:0.16rem' : 'background-position:center'"></i></h5>
                    <p v-show="is_host">${user.wealth_value}贡献</p>
                </td>
            </tr>
        </table>
    </div>
    <div class="bangdan_bottom" v-if="users.length">
        <span v-if="current_rank">我目前排名<b>${current_rank}</b>位</span>
        <span v-if="!current_rank">暂未上榜</span>
    </div>

</div>

<script>
    var opts = {
        data: {
            sid: '{{ sid }}',
            code: '{{ code }}',
            room_id: {{ room_id }},
            user_id: '{{ user_id }}',
            cur_idx: 0,
            ranking_tab: ['日榜', '周榜'],
            page: 1,
            total_page: 1,
            users: [],
            current_rank: 0,
            is_host:false,
        },
        created: function () {
            this.list();
        },
        methods: {
            list: function () {
                if (this.page > this.total_page) {
                    return
                }
                var data = {
                    page: this.page,
                    per_page: 10,
                    sid: this.sid,
                    code: this.code,
                    room_id: this.room_id
                };
                if (this.cur_idx == 0) {
                    data.list_type = 'day';
                    data.per_page = 10;
                } else if (this.cur_idx == 1) {
                    data.list_type = 'week';
                    data.per_page = 20;
                }
                $.authGet('/m/rooms/find_wealth_rank_list', data, function (resp) {
                    //console.log(resp);
                    if (resp.error_code == 0) {
                        vm.total_page = resp.total_page;
                        vm.current_rank = resp.current_rank;
                        vm.is_host = resp.is_host;
                        $.each(resp.users, function (index, item) {
                            item.level_img = "/m/images/level_" + item.level + '.png';
                            vm.users.push(item);
                        })
                    }
                });
                this.page++;
            },
            rankingSelect: function (index) {
                this.cur_idx = index;
                this.current_rank = 0;
                this.users = [];
                this.page = 1;
                this.total_page = 1;
                this.list();
            },
            userDetail: function (user_id) {
                if(this.user_id == user_id){
                    location.href = "app://users/detail?id=" + user_id;
                } else {
                    location.href = "app://users/other_detail?user_id=" + user_id;
                }
            }
        }
    };

    vm = XVue(opts);

    $(function () {
        $(window).scroll(function () {
            if ($(document).scrollTop() >= $(document).height() - $(window).height()) {
                vm.list();
            }
        });
    })
</script>

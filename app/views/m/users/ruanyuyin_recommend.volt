{{ block_begin('head') }}
{{ theme_css('/m/ruanyuyin/css/recommend.css','/m/css/room_password_pop') }}
{{ theme_js('/m/ruanyuyin/js/font_rem.js','/m/js/room_password_pop','/m/js/add_friend') }}
{{ block_end() }}
<div class="int_box" id="app" v-cloak>

    <div class="int_list" v-for="(item,index) in user_list">
        <div class="pic">
            <img :src="item.avatar_url" alt="" @click.stop="userDetail(item.id)"/>
        </div>
        <div class="text">
            <div class="left interest_tags">
                <h3> ${ item.nickname }</h3>
                <p class="int_nv"><span  v-for="tag in item.tags">${ tag.text }</span></p>
                <b class="interest_brief"> ${ item.recommend_tip }</b>
            </div>
        </div>

        <div class="btn_list">
            <span  @click.stop="enterRoom(item.current_room_id)" v-show="item.current_room_id"><i class="room"></i>添加</span>
            <span  @click="addFriend(item)" :class="{'interest_add':true,'interest_add_to':!item.is_added}"><i class="friend"></i>${ item.is_added? "已添加":"添加" } </span>
        </div>
        <div class="line_bottom"></div>
    </div>
    <div class="int_change" @click.stop="changeUsers">
        <span>换一换</span>
    </div>

    {#密码弹框#}
    <div class="room_cover">
        <div class="room_pop">
            <img class="room_pop_bg" src="/m/images/room_pop_bg.png" alt="">
            <div class="room_locked">房间已上锁</div>
            <div class="room_lock">
                <label for="">密码</label>
                <input class="input_text" maxlength="10" type="number" placeholder="最多输入10个字" id="password" style="
    background-color: #F0F0F0;">
            </div>
            <div class="room_btn">
                <span class="room_out">取消</span>
                <span class="room_in">进入房间</span>
            </div>
        </div>
    </div>

    {#介绍弹框#}
    <div class="fudong fd_dashang">
        <div class="ask_box">
            <h1>申请加为好友</h1>
            <div class="ask_text">
                <textarea placeholder="介绍一下自己吧" class="weui_textarea" onkeyup="wordStatic(this);" maxlength="15"
                          id="self_introduce"></textarea>
                <div class="weui_textarea_counter"><span id="num">0</span>/15</div>
            </div>
            <div class="ask_btn close_btn">
                <span>确定</span>
            </div>
        </div>
    </div>
    <div class="fudong_bg"></div>

</div>

<script>
    sid = '{{ sid }}';
    code = '{{ code }}';

    var opts = {
        data: {
            room_hidden:true,
            friend_hidden:true,
            ico_male: "images/ico_male.png",
            ico_female: "images/ico_female.png",
            page: 1,
            user_list: []
        },
        created: function () {
            this.userList();
        },
        methods: {
            userList: function () {
                var data = {
                    page: this.page,
                    per_page: 6
                };
                console.log(this.page);
                $.authGet('/m/users/user_list?sid={{ sid }}&code={{ code }}', data, function (resp) {
                    if (resp.user_list) {
                        vm.user_list = [];
                        $.each(resp.user_list, function (index, item) {
                            item.is_added = false;
                            vm.user_list.push(item);
                        })
                    } else {
                        alert("暂无推荐");
                    }

                });
                this.page++;
            },
            changeUsers: function () {
                this.userList();
            },
            addFriend: function (item) {
                open_fd();
                selected_user = item;
            },
            userDetail: function (id) {
                console.log(id);
                location.href = "app://users/other_detail?user_id=" + id;
            },
            enterRoom: function (id) {
                var data = {room_id: id, sid: sid, code: code};
                $.authPost("/m/unions/is_need_password", data, function (resp) {
                    if (resp.error_code == 0) {
                        selected_room_id = id;
                        $('.room_cover').show();
                    } else {
                        location.href = "app://rooms/detail?id=" + id;
                    }
                });
            }
        }
    };

    vm = XVue(opts);


</script>
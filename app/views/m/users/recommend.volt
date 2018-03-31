{{ block_begin('head') }}
{{ theme_css('/m/css/recommend_main','/m/css/recommend','/m/css/room_password_pop','/m/css/self_introduce_pop') }}
{{ theme_js('/m/js/room_password_pop','/m/js/add_friend') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <div class="interest-list">
        <ul>
            <li v-for="(item,index) in user_list" >
                <div class="list_left">
                    <img class="interest_avatar" :src="item.avatar_url" alt="" @click.stop="userDetail(item.id)"/>
                    <div class="interest_info">
                        <div class="interest_title">
                            <span class="interest_name"> ${ item.nickname }</span>
                        </div>
                        <div class="interest_tags">
                        <span class="interest_tag" v-for="tag in item.tags"
                              :style="{'backgroundColor':tag.color} ">${ tag.text }</span>
                        </div>
                        <span class="interest_brief" v-if="item.monologue"> ${ item.monologue }</span>
                    </div>
                </div>
                <div class="list_right">

                    <img class="ico-anchor" v-show="item.current_room_id" src="/m/images/ico-anchor.png" alt=""
                         @click.stop="enterRoom(item.current_room_id)">

                    <span :class="{'interest_add':true,'interest_add_to':!item.is_added}"
                          @click="addFriend(item)">${ item.is_added? "已添加":"＋ 好友" } </span>
                </div>

                <div class="line_bottom"></div>
            </li>
        </ul>
    </div>

    <div class="btn_batch" @click.stop="changeUsers">
        <img class="ico-batch" src="/m/images/ico-batch.png" alt="">
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
    <div class="fudong_bg" ></div>

</div>


<script>
    sid = '{{ sid }}';
    code = '{{ code }}';

    var opts = {
        data: {
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
{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/family_info','/m/css/union_pop') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <div class="family_info">
        <img class="family-more" :src="ico_more" alt="" v-show="union.id == user.union_id">
        <div class="family_top">
            <div class="family_top_left">
                <img class="family-ico" src="{{ union.avatar_url }}" alt="">
                <div class="family_name">
                    <span>   {{ union.name }}</span>
                    <span class="family_id">家族ID:{{ union.id }} </span>
                </div>
            </div>
            <div class="family_top_right">
                <div class="family_prestige">
                    <span>声望 {{ union.fame_value }}</span>
                </div>
            </div>
        </div>
        <div class="family_slogan">
            {{ union.notice }}
        </div>
    </div>
    <div class="new_member" v-if="is_president" @click.stop="applicationList">
        <div class="new_member_title">新的成员</div>
        <div class="new_member_right">
            <span class="new_dot" v-show="{{ union.new_apply_num }}"></span>
            <img class="arrow-right" src="/m/images/arrow-right.png" alt="">
        </div>
    </div>
    <ul class="member_tab" id="member_tab">
        <li v-if="!is_president" v-for="(item,index) in tab" class="member_only" v-show="!index"> ${item} <span
                    v-if="!index">(${total_entries})</span></li>
        <li v-if="is_president" v-for="(item,index) in tab" :class="[cueIdx===index?'active':'']"
            @click="tabClick(index)"> ${item} <span v-if="!index">(${total_entries})</span></li>
    </ul>
    <ul class="member_list" v-show="cueIdx==0">
        <li>
            <div class="member_left">
                <img class="member_avatar" src="{{ president.avatar_small_url }}" alt=""
                     @click="userOperation(president)">
                <div class="member_name">
                    <div class="name">
                        <span> ${president.nickname}</span>
                        <span class="female" v-if="president.sex == 1">${president.age}</span>
                        <span class="male" v-if="president.sex == 0">${president.age}</span>
                        <span class="president">会长</span>
                    </div>
                    <div class="slogan">
                        ${president.monologue}
                    </div>
                </div>
            </div>
            <div class="member_right">
                <img v-if="president.current_room_id" class="flag_manage" :src="flag_manage" alt=""
                     @click="roomDetail(president.current_room_id)">
                <span v-if="!president.manage" class="member_time">${president.time}</span>
            </div>
        </li>
        <li v-for="(member,index) in member_list">
            <div class="member_left">
                <img class="member_avatar" :src="member.avatar_small_url" alt="" @click="userOperation(member)">
                <div class="member_name">
                    <div class="name">
                        <span> ${member.nickname}</span>
                        <span class="female" v-if="member.sex == 1">${member.age}</span>
                        <span class="male" v-if="member.sex == 0">${member.age}</span>
                        <span class="president" v-if="member.id == union.user_id">会长</span>
                    </div>
                    <div class="slogan">
                        ${member.monologue}
                    </div>

                </div>
            </div>
            <div class="member_right">
                <img v-if="member.current_room_id" class="flag_manage" :src="flag_manage" alt=""
                     @click="roomDetail(member.current_room_id)">
                <span v-if="!member.manage" class="member_time">${member.time}</span>
            </div>
        </li>
    </ul>
    <ul class="member_list" v-show="cueIdx===1">
        <li v-for="(member,index) in member_list">
            <div class="member_left">
                <img class="member_avatar" :src="member.avatar_small_url" alt="">
                <div class="member_name">
                    <div class="name">
                        <span> ${member.nickname}</span>
                        <span class="female" v-if="member.sex == 1">${member.age}</span>
                        <span class="president" v-if="member.id == union.user_id">会长</span>
                    </div>
                    <div class="slogan">${member.monologue}</div>
                </div>
            </div>
            <div class="member_right">
                <div class="member_charm">
                    <span class="charm_tit">魅力值</span>
                    <span class="charm_num">${member.union_charm_value}</span>
                </div>
            </div>
        </li>
    </ul>
    <ul class="member_list" v-show="cueIdx===2">
        <li v-for="(member,index) in member_list">
            <div class="member_left">
                <img class="member_avatar" :src="member.avatar_small_url" alt="">
                <div class="member_name">
                    <div class="name">
                        <span> ${member.nickname}</span>
                        <span class="female" v-if="member.sex == 1">${member.age}</span>
                        <span class="president" v-if="member.id == union.user_id">会长</span>
                    </div>
                    <div class="slogan">${member.monologue}</div>
                </div>
            </div>
            <div class="member_right">
                <div class="member_wealth">
                    <span class="wealth_tit">财富值</span>
                    <span class="wealth_num">${member.union_wealth_value}</span>
                </div>
            </div>
        </li>
    </ul>
    <div class="family_info_box" v-if="!user.union_id" @click.stop="applyJoinUnion()">
        <div class="info_btn">
            <p>申请加入</p>
        </div>
    </div>

    <div class="pop_bottom_bg"></div>
    <div class="pop_bottom">
        <ul>
            <li v-show="!user_operation && is_president" @click.stop="edit">修改家族资料</li>
            <li v-show="!user_operation " @click.stop="rankUnion">家族排行</li>
            <li v-show="!user_operation && is_president" @click.stop="applyGoHot">上热门</li>
            <li v-show="!user_operation && is_president" @click.stop="confirmPop">解散家族</li>
            <li v-show="user_operation && is_president" @click.stop="userDetail">查看资料</li>
            <li v-show="user_operation && selected_user.id != union.user_id && is_president" @click.stop="confirmPop">
                踢出家族
            </li>
            <li v-show="!is_president" @click.stop="confirmPop">退出家族</li>
        </ul>
        <div class="close_btn">取消</div>
    </div>

    <div class="middle_pop">
        <div class="close_btn" id="middle_close_btn"></div>
        <p v-show="!user_operation && is_president">确认解散家族，解散后，不可恢复！</p>
        <p v-show="user_operation && is_president">确认将${selected_user.nickname}踢出家族</p>
        <p v-show="!is_president">确认退出家族，退出后，您在家族中的魅力值、土豪值将被清零？</p>
        <div class="middle_btn" v-show="!user_operation && is_president" id="dissolution">确认解散</div>
        <div class="middle_btn" v-show="user_operation && is_president" @click.stop="kickUser">确认踢出</div>
        <div class="middle_btn" v-show="!is_president" @click.stop="exitUnion">确认退出</div>
    </div>
    <div class="middle_pop_bg"></div>
</div>

<script>
    var opts = {
        data: {
            union: {{ union }},
            president: {{ president }},
            user: {{ user }},
            cueIdx: 0,
            is_president: {{ is_president }},
            flag_manage: '/m/images/flag-manage.png',
            flag_president: '/m/images/flag-president.png',
            ico_more: '/m/images/ico-more.png',
            tab: ["成员", "魅力榜", "土豪榜"],
            sid: '{{ sid }}',
            code: '{{ code }}',
            page: 1,
            total_page: 1,
            total_entries: 0,
            member_list: [],
            can_apply: true,
            user_operation: true,
            selected_user: {{ user }},
            selected_index: 0
        },
        created: function () {
            this.memberList(0);
        },
        methods: {
            rankUnion: function () {
                console.log('aa');
                var url = "/m/unions/rank&sid=" + '{{ sid }}' + "&code=" + '{{ code }}';
                location.href = url;
            },
            tabClick: function (index) {
                this.cueIdx = index;
                this.member_list = [];
                this.page = 0;
                this.memberList(index);
            },
            memberList: function (index) {
                if (this.page > this.total_page) {
                    return;
                }
                var data = {union_id:{{ union.id }}, page: this.page, per_page: 10, sid: this.sid, code: this.code};
                if (index == 0) {
                    data.order = "current_room_id desc";
                    data.filter_id = this.president.id;
                } else if (index == 1) {
                    data.order = "union_charm_value desc";
                } else if (index == 2) {
                    data.order = "union_wealth_value desc";
                }
                this.selected_index = index;
                $.authGet('/m/unions/users', data, function (resp) {
                    vm.total_page = resp.total_page;
                    vm.total_entries = resp.total_entries;
                    $.each(resp.users, function (index, item) {
                        vm.member_list.push(item);
                    })
                });
                this.page++;
            },
            userOperation: function (user) {
                if (!this.is_president) {
                    return;
                }
                this.user_operation = true;
                this.selected_user = user;
                $('.pop_bottom').show();
                $('.pop_bottom_bg').show();
            },
            userDetail: function () {
                console.log(this.selected_user.id);
                if (this.selected_user.id == this.user.id) {
                    url = "app://users/detail";
                } else {
                    url = "app://users/other_detail?user_id=" + this.selected_user.id;
                }
                console.log(url);
                location.href = "app://users/other_detail?user_id=" + this.selected_user.id;
            },
            roomDetail: function (id) {
                var url = "app://rooms/detail?id=" + id;
                location.href = url;
            },
            applyJoinUnion: function () {
                if (vm.can_apply == false) {
                    return false;
                }
                vm.can_apply = false;
                var url = "/m/unions/apply_join_union";
                var data = {union_id: this.union.id, sid: this.sid, code: this.code};
                $.authPost(url, data, function (resp) {
                    vm.can_apply = true;
                    alert(resp.error_reason);
                    if (resp.error_url) {
                        location.href = resp.error_url;
                    } else if (resp.error_code == 0) {
                        window.history.back();
                    }
                });
            },
            applicationList: function () {
                var url = "/m/unions/new_users&sid=" + this.sid + "&code=" + this.code;
                location.href = url;
            },
            confirmPop: function () {
                $('.pop_bottom').hide();
                $('.pop_bottom_bg').hide();
                $(".middle_pop").show();
                $(".middle_pop_bg").show();
            },
            kickUser: function () {
                var url = "/m/unions/kicking";
                var data = {user_id: this.selected_user.id, sid: this.sid, code: this.code};
                $.authPost(url, data, function (resp) {
                    alert(resp.error_reason);
                    if (resp.error_code == 0) {
                        location.reload();
                    }
                });
            },
            exitUnion: function () {
                var url = "/m/unions/exit_union";
                var data = {union_id: this.union.id, sid: this.sid, code: this.code};
                $.authPost(url, data, function (resp) {
                    if (resp.error_code == 0) {
                        var url = "/m/unions/index&sid=" + vm.sid + "&code=" + vm.code;
                        location.href = url;
                    } else {
                        alert(resp.error_reason);
                    }
                });
            },
            edit: function () {
                var url = "/m/unions/edit&sid=" + this.sid + "&code=" + this.code;
                location.href = url;
            },
            applyGoHot: function () {
                var url = "/m/unions/apply_go_hot&sid=" + this.sid + "&code=" + this.code;
                location.href = url;
            }
        }
    };
    vm = XVue(opts);
    /*导航吸顶效果*/
    var obj = document.getElementById("member_tab");
    var ot = obj.offsetTop;
    document.onscroll = function () {
        var st = document.body.scrollTop || document.documentElement.scrollTop;
        obj.setAttribute("data-fixed", st >= ot ? "fixed" : "");
    };

    $(function () {
        function close_mp() {
            $(".middle_pop").hide();
            $(".middle_pop_bg").hide();
        }

        function close_pb() {
            $('.pop_bottom').hide();
            $('.pop_bottom_bg').hide();
        }

        function dissolution(status, _this) {
            var data = {
                sid: "{{ sid }}",
                code: "{{ code }}"
            };
            $.authPost("/m/unions/dissolution_union", data, function (resp) {
                if (resp.error_code == 0) {
                    var url = "/m/unions/index&sid=" + vm.sid + "&code=" + vm.code;
                    location.href = url;
                } else {
                    alert(resp.error_reason);
                }
            });
        }

        close_mp();
        close_pb();

        var doc_height = $(document).height();
        var w_height = $(window).height();
        var w_width = $(window).width();


        $(".fudong_bg").attr("style", "height:" + doc_height + "px");
        var div_width = $(".middle_pop").width();
        var div_height = $(".middle_pop").height();

        var div_left = w_width / 2 - div_width / 2 + "px";
        var div_top = w_height / 2 - div_height / 2 + "px";

        $(".middle_pop").css({
            "left": div_left,
            "top": div_top
        });

        $(window).scroll(function () {
            if ($(document).scrollTop() >= $(document).height() - $(window).height()) {
                vm.memberList(vm.selected_index);
            }
        });

        $('.family-more').click(function () {
            vm.user_operation = false;
            $('.pop_bottom').show();
            $('.pop_bottom_bg').show();
        });

        $('.close_btn').click(function () {
            $('.pop_bottom').fadeOut('1000');
            $('.pop_bottom_bg').fadeOut('1000');
        });

        $("#middle_close_btn").click(function () {
            close_mp();
        });

        $("#dissolution").click(function () {
            dissolution();
            close_mp();
            close_pb();
        });
    })

</script>

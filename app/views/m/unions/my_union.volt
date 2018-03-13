{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/family_info') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <div class="family_info">
        <img class="family-more" :src="ico_more" alt="" @click="moreShow">
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
    <div class="new_member" v-if="is_president">
        <div class="new_member_title">新的成员</div>
        <div class="new_member_right">
            <span class="new_dot"></span>
            <img class="arrow-right" src="/m/images/arrow-right.png" alt="">
        </div>
    </div>
    <ul class="member_tab" id="member_tab">
        <li v-if="!is_president" v-for="(item,index) in tab" class="member_only" v-show="!index"> ${item} <span
                    v-if="!index">(${member_list.length})</span></li>
        <li v-if="is_president" v-for="(item,index) in tab" :class="[cueIdx===index?'active':'']"
            @click="tabClick(index)"> ${item} <span v-if="!index">(${member_list.length})</span></li>
    </ul>
    <ul class="member_list" v-show="cueIdx==0">
        <li v-for="(member,index) in member_list">
            <div class="member_left">
                <img class="member_avatar" :src="member.avatar_small_url" alt="">
                <div class="member_name">
                    <div class="name">
                        <span> ${member.nickname}</span>
                        <span class="female" v-if="member.sex == 1">
                            ${member.age}
                        </span>
                        <span class="president" v-if="member.id == union.user_id">
                           会长
                        </span>
                    </div>
                    <div class="slogan">
                        ${member.monologue}
                    </div>

                </div>
            </div>
            <div class="member_right">
                <img v-if="member.current_room_id" class="flag_manage" :src="flag_manage" alt="" @click="roomDetail(member.current_room_id)">
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
                    <span class="charm_num">${member.charm_value}</span>
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
                    <span class="wealth_num">${member.wealth_value}</span>
                </div>
            </div>
        </li>
    </ul>
</div>

<script>
    var opts = {
        data: {
            union: {{ union }},
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
            member_list: []
        },
        created: function () {
            this.memberList();
        },
        methods: {
            moreShow: function () {
                console.log(111);
            },
            tabClick: function (index) {
                this.cueIdx = index
            },
            memberList: function () {
                var data = {union_id:{{ union.id }}, page: this.page, per_page: 20, sid: this.sid, code: this.code};
                $.authGet('/m/unions/users', data, function (resp) {
                    vm.member_list = [];
                    vm.total_page = resp.total_page;
                    vm.total_entries = resp.total_entries;
                    vm.member_list = resp.users;
                });
            },
            roomDetail: function (id) {
                var url = "app://rooms/detail?id=" + id;
                console.log(url);
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
        obj.setAttribute("data-fixed", st >= ot ? "fixed" : "")
    }
</script>

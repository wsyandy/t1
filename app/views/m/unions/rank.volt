{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/family_ranking') }}
{{ block_end() }}


<div class="vueBox" id="app" v-cloak="">
    <ul class="ranking_tab">
        <li v-for="(item,index) in ranking_tab" @click='rankingSelect(index)'>
            <span :class="{'active':cur_idx===index}">${item}</span>
        </li>
    </ul>
    <div class="family-list" v-show="cur_idx==0">
        <ul>
            <li v-for="(item,index) in union_list" @click.stop="unionDetail(item.id)">
                <div class="list_left">
                    <div class="family_order">
                        <img v-show="index<3" :src="index<2?(index<1?ranking_1:ranking_2):ranking_3" alt="">
                        <div v-show="index>2" class="family_flag"> ${ index+1 }</div>
                    </div>

                    <img class="family_avatar" :src="item.avatar_small_url" alt="">
                    <div class="family_info">
                        <span class="family_name"> ${ item.name }</span>
                        <div class="family_prestige">
                            <span>声望${ item.fame_value }</span>
                        </div>
                    </div>
                </div>
                <div class="list_right">
                    <span class="family_number">${ item.user_num } </span>
                    <span class="family_member">成员 </span>
                </div>
            </li>
        </ul>
    </div>
    <div class="family-list" v-show="cur_idx==1">
        <ul>
            <li v-for="(item,index) in union_list" @click.stop="unionDetail(item.id)">
                <div class="list_left">
                    <div class="family_order">
                        <img v-show="index<3" :src="index<2?(index<1?ranking_1:ranking_2):ranking_3" alt="">
                        <div v-show="index>2" class="family_flag"> ${ index+1 }</div>
                    </div>

                    <img class="family_avatar" :src="item.avatar_small_url" alt="">
                    <div class="family_info">
                        <span class="family_name"> ${ item.name }</span>
                        <div class="family_prestige">
                            <span>声望${ item.fame_value }</span>
                        </div>
                    </div>
                </div>
                <div class="list_right">
                    <span class="family_number">${ item.user_num } </span>
                    <span class="family_member">成员 </span>
                </div>
            </li>
        </ul>
    </div>
    <div class="my_ranking" v-show="!cur_idx">
        我的家族日榜排名 <span class="num">${my_rank}</span> 位
    </div>
    <div class="my_ranking" v-show="cur_idx">
        我的家族周榜排名 <span class="num">${my_rank}</span> 位
    </div>

</div>

<script>
    var opts = {
        data: {
            sid: '{{ sid }}',
            code: '{{ code }}',
            ranking_1: "/m/images/ranking_1.png",
            ranking_2: "/m/images/ranking_2.png",
            ranking_3: "/m/images/ranking_3.png",
            cur_idx: 0,
            ranking_tab: ['日榜', '周榜'],
            rankingLst: [],
            page: 1,
            total_page: 1,
            union_list: [],
            my_rank: 1
        },
        created: function () {
            this.list();
        },
        methods: {
            list: function () {
//                if (this.page > this.total_page) {
//                    return
//                }
                var data = {
                    page: this.page,
                    per_page: 10,
                    sid: this.sid,
                    code: this.code
                };
                if (this.cur_idx == 0) {
                    data.list_type = 'day';
                    data.per_page = 10;
                } else if (this.cur_idx == 1) {
                    data.list_type = 'week';
                    data.per_page = 20;
                }
                $.authGet('/m/unions/fame_value_rank_list', data, function (resp) {
                    vm.total_page = resp.total_page;
                    vm.my_rank = resp.my_rank;
                    $.each(resp.unions, function (index, item) {
                        vm.union_list.push(item);
                    })
                });
//                this.page++;
            },
            unionDetail: function (id) {
                var url = "/m/unions/my_union&sid=" + '{{ sid }}' + "&code=" + '{{ code }}' + '&union_id=' + id;
                location.href = url;
            },
            rankingSelect: function (index) {
                this.cur_idx = index;
                this.my_rank = 1;
                this.union_list = [];
                this.list();
            }
        }
    };

    vm = XVue(opts);

    //    $(function () {
    //        $(window).scroll(function () {
    //            if ($(document).scrollTop() >= $(document).height() - $(window).height()) {
    //                vm.list();
    //            }
    //        });
    //    })
</script>

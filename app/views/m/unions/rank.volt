{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/family_ranking') }}
{{ block_end() }}


<div class="vueBox" id="app" v-cloak="">
    <div class="family-list">
        <ul>
            <li v-for="(item,index) in union_list">
                <div class="list_left">
                    <div class="family_order">
                        <img v-show="index<3" :src="index<2?(index<1?ranking_1:ranking_2):ranking_3" alt="">
                        <div v-show="index>2" class="family_flag"> ${ index+1 }</div>
                    </div>

                    <img class="family_avatar" :src="item.avatar_url" alt="">
                    <div class="family_info">
                        <span class="family_name"> ${ item.name }</span>
                        <span class="family_prestige"> 声望${ item.fame_value }</span>
                    </div>
                </div>
                <div class="list_right">
                    <span class="family_number">${ item.user_num } </span>
                    <span class="family_member">成员 </span>
                </div>
            </li>
        </ul>
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
            rankingLst: [],
            page: 1,
            total_page: 1,
            total_entries: 0,
            union_list: []
        },
        created: function () {
            this.list();
        },
        methods: {
            list: function () {
                var data = {
                    type: 2,
                    order: "fame_value desc",
                    page: this.page,
                    per_page: 10,
                    sid: this.sid,
                    code: this.code
                };
                $.authGet('/m/unions/search', data, function (resp) {
                    vm.union_list = [];
                    vm.total_page = resp.total_page;
                    vm.total_entries = resp.total_entries;
                    vm.union_list = resp.unions;
                });
            }
        }
    };

    vm = XVue(opts);
</script>

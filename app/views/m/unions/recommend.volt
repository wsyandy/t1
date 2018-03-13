{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/family_heart') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <div class="family-search">
        {#<input type="text" class="input-search" v-model="searchText" placeholder="请输入家族ID或昵称">#}
        <div class="text-search">
            <input type="number" class="input-search" v-model="searchText" placeholder="请输入家族ID">
            <img v-show="searchText" class="ico-clear" src="/m/images/ico-clear.png" alt="" @click="clearSearch()">
        </div>
        <div class="btn-search">
            <img class="ico-search" src="/m/images/ico-search.png" alt="" @click="searchFamily()">
        </div>
    </div>
    <div class="family-list">
        <ul>
            <li v-for="item in unions">
                <div class="list_left">
                    <img class="family_avatar" :src="item.avatar_url" alt="" @click.stop="unionDetail(item.id)">
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
            unions: [],
            page: 1,
            total_page: 1,
            total_entries: 0,
            searchText: ''
        },
        created: function () {
            this.list();
        },
        methods: {
            list: function () {
                if (this.searchText) {
                    var data = {
                        search_value: this.searchText,
                        type: 2,
                        recommend: 0,
                        page: this.page,
                        per_page: 10,
                        sid: this.sid,
                        code: this.code
                    };
                } else {
                    var data = {type: 2, recommend: 1, page: this.page, per_page: 10, sid: this.sid, code: this.code};
                }
                $.authGet('/m/unions/search', data, function (resp) {
                    vm.unions = [];
                    vm.total_page = resp.total_page;
                    vm.total_entries = resp.total_entries;
//                    $.each(resp.unions, function (index, item) {
//                        vm.unions.push(item);
//                    });
                    vm.unions = resp.unions;
                    console.log(resp.unions);
                });
            },
            clearSearch: function () {
                this.searchText = "";
            },
            searchFamily: function () {
                this.list();
            },
            unionDetail: function (id) {
                var url = "/m/unions/my_union&sid=" + '{{ sid }}' + "&code=" + '{{ code }}' + '&union_id=' + id;
                location.href = url;
            }
        }
    };
    vm = XVue(opts);
</script>
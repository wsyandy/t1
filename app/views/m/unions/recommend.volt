{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/family_heart', '/m/css/family_ranking') }}
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
            <li v-for="item, index in unions" @click.stop="unionDetail(item.id)">
                <div class="list_left">
                    <!--<div class="family_order" v-show="searchText == ''">
                        <img v-show="index<3" :src="index<2?(index<1?ranking_1:ranking_2):ranking_3" alt="">
                    </div>-->
                    <div :class="index<3?'list_num list_num'+index:'list_num'">
                        <span>${index+1}</span>
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
        <div class="top_five" v-show="searchText">
            昨天家族声望日榜前五名
        </div>
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
            searchText: '',
            ranking_1: "/m/images/ranking_1.png",
            ranking_2: "/m/images/ranking_2.png",
            ranking_3: "/m/images/ranking_3.png"
        },
        created: function () {
            this.list();
        },
        methods: {
            list: function () {
                if (this.page > this.total_page) {
                    return;
                }
                var data = {
                    recommend: 1,
                    order: 'created_at desc',
                    page: this.page,
                    per_page: 10,
                    sid: this.sid,
                    code: this.code
                };
                if (this.searchText) {
                    data.search_value = this.searchText;
                    data.recommend = 0;
                } else {
                    data.recommend = 1;
                }
                $.authGet('/m/unions/search', data, function (resp) {
                    vm.total_page = resp.total_page;
                    if (resp.unions) {
                        $.each(resp.unions, function (index, item) {
                            vm.unions.push(item);
                        });
                    }
                    if (vm.searchText && vm.unions.length == 0) {
                        alert("没有搜索到家族");
                    }
                });
                this.page++;
            },
            clearSearch: function () {
                this.searchText = "";
            },
            searchFamily: function () {
                this.page = 1;
                this.unions = [];
                this.list();
            },
            unionDetail: function (id) {
                var url = "/m/unions/my_union&sid=" + '{{ sid }}' + "&code=" + '{{ code }}' + '&union_id=' + id;
                location.href = url;
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
    });
</script>
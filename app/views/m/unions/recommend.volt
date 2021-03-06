{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/family_heart', '/m/css/family_ranking') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <div class="family-search">
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
                    <div :class="index<3?'list_num list_num'+index:'list_num'" v-show="is_recommend">
                        <span>${index+1}</span>
                    </div>
                    <img class="family_avatar" :src="item.avatar_small_url" alt="">
                    <div class="family_info">
                        <span class="family_name"> ${ item.name }  <img class="ico_level"
                                                                        :src="union_level_images[item.union_level]"
                                                                        alt="" v-if="item.union_level>0"></span>

                        <div class="family_prestige">
                            <span>声望${ item.fame_value }</span>
                        </div>
                    </div>
                </div>
                <div class="list_right">
                    <p class="family_number">${ item.user_num } </p>
                    <p class="family_member">成员 </p>
                </div>
            </li>
        </ul>
        <div class="top_five" v-show="is_recommend && show_tip">
            <P>推荐家族活动：</P>
            <p>1.家族前一天声望值最高的前五位家族，将获得当天家族推荐位。</p>
            <p>2.获得家族推荐位的家族，前一天家族声望值越高，当日推荐家族排名越靠前。</p>
            <p>3.推荐家族排名每天0点更新。</p>
        </div>
    </div>
</div>

<script>
    var opts = {
        data: {
            sid: '{{ sid }}',
            code: '{{ code }}',
            show_tip: {{ show_tip }},
            unions: [],
            page: 1,
            total_page: 1,
            searchText: '',
            ranking_1: "/m/images/ranking_1.png",
            ranking_2: "/m/images/ranking_2.png",
            ranking_3: "/m/images/ranking_3.png",
            is_recommend: true,
            union_level_images:{{ union_level_images }}
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
                    this.is_recommend = false;
                } else {
                    data.recommend = 1;
                    this.is_recommend = true;
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
            // if ($(document).scrollTop() >= $(document).height() - $(window).height()) {
            //     vm.list();
            // }
        });
    });
</script>
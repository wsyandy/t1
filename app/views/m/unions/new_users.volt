{{ block_begin('head') }}
{{ theme_css('/m/css/union_main','/m/css/application_details') }}
{{ block_end() }}

<div class="vueBox" id="app" v-cloak>
    <div class="application_list_box">
        <div class="application_list" v-for="item in application_list">
            <div class="list_img">
                <img :src="item.avatar_small_url">
            </div>
            <div class="list_message">
                <div class="name">
                    <h3>${item.nickname}
                        <span :class="[item.sex ? 'men':'women']">${item.age}</span></h3>
                    <p v-if="item.is_exit_union">申请 <span class="get_out_family">退出家族</span></p>
                    <p v-else>申请 <span>加入家族</span></p>
                </div>
                <div :class="[item.apply_status?'list_selected':'','list_agree']" @click.stop="applicationDetail(item)">
                    ${item.apply_status_text}
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    var opts = {
        data: {
            sid: '{{ sid }}',
            code: '{{ code }}',
            page: 1,
            total_page: 1,
            application_list: []
        },
        created: function () {
            this.applicationList();
        },
        methods: {
            applicationList: function () {
                if (this.page > this.total_page) {
                    return;
                }

                var data = {page: this.page, per_page: 10, sid: this.sid, code: this.code};
                $.authGet('/m/unions/application_list', data, function (resp) {
                    vm.total_page = resp.total_page;
                    $.each(resp.users, function (index, item) {
                        vm.application_list.push(item);
                    })
                });
                this.page++;
            },
            applicationDetail: function (item) {

                if (item.apply_status) {
                    return;
                }

                var url = "/m/unions/application_detail?sid=" + this.sid + "&code=" + this.code + "&user_id=" + item.id;
                if (item.is_exit_union) {
                    var url = "/m/unions/apply_exit?sid=" + this.sid + "&code=" + this.code + "&user_id=" + item.id;
                }

                location.href = url;
            }
        }
    };
    vm = XVue(opts);

    $(function () {
        $(window).scroll(function () {
            if ($(document).scrollTop() >= $(document).height() - $(window).height()) {
                vm.applicationList();
            }
        });
    })
</script>
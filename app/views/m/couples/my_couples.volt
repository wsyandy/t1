{{ block_begin('head') }}
{{ theme_css('/m/css/cp_apple.css','/m/css/my_cp.css') }}
{{ theme_js('/m/js/cp_resize.js') }}
{{ block_end() }}
<div class="vueBox" id="app">
    <ul class="mycp_list">
        <li v-for="other_user in other_users" @click="catMarriage(other_user.id)">
            <div class="mycp_list_left">
                <div class="mycp_avatar">
                    <img :src="user.avatar_url" alt="">
                </div>
                <div class="mycp_name" v-text="user.nickname"></div>
            </div>
            <div class="mycp_list_center">
                <div class="cp_cer"> CP证</div>
                <div class="cp_value"><span>情侣值：</span> <span v-text="other_user.cp_value?other_user.cp_value:0"></span>
                </div>
                <img class="cp_heart" :src="cp_heart" alt="">

            </div>
            <div class="mycp_list_right">
                <div class="mycp_avatar">
                    <img :src="other_user.avatar_url" alt="">
                </div>
                <div class="mycp_name" v-text="other_user.nickname"></div>
            </div>

        </li>
    </ul>
</div>
<script>
    var opts = {
        data: {
            cp_heart: '/m/images/cp_heart.png',
            sid: "{{ sid }}",
            code: "{{ code }}",
            user:{{ user }},
            other_users: []


        },
        created: function () {
            var data = {
                sid: "{{ sid }}",
                code: "{{ code }}"
            };
            $.authPost('/m/couples/my_couples', data, function (resp) {
                $.each(resp.users, function (index, item) {
                    vm.other_users.push(item);
                });
            })
        },
        methods: {
            catMarriage: function (other_user_id) {
                var url = '/m/couples/marriage?other_user_id=' + other_user_id + '&sid=' + vm.sid + '&code=' + vm.code;
                window.location.href = url;
            }
        }
    };

    vm = XVue(opts);
</script>

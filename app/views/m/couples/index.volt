{{ block_begin('head') }}
{{ theme_css('/m/css/cp_apple.css','/m/css/cp_index.css') }}
{{ theme_js('/m/js/cp_resize.js') }}
{{ block_end() }}
<div class="vueBox" id="app">
    <div class="cp_head">
        <div class="cp_info">
            <div class="cp_avatar">
                <img :src="room_host_user.avatar_url" alt="">
                <span class="cp_name" v-text="room_host_user.nickname">  </span>
            </div>
            <div class="cp_id">
                <span>ID：</span>
                <span v-text="room_host_user.uid"></span>
            </div>

        </div>
        <div class="cp_heart">
            <img :src="cp_heart" alt="">
        </div>
        <div class="cp_info">
            <div class="cp_avatar">
                <img :src="pursuer.avatar_url" alt="">
                <span class="cp_name" v-text="pursuer.nickname">  </span>
            </div>
            <div class="cp_id">
                <span v-if="pursuer.uid">ID：</span>
                <span v-text="pursuer.uid"></span>
            </div>
        </div>
    </div>
    <div class="cp_btn" :class="{'cp_btn_on':pursuer.uid}" @click="YseIDo()">
        <span>我愿意</span>
    </div>
    {% if is_show_my_cp %}
        <a class="cp_my" href="/m/couples/my_couples?sid={{ sid }}&code={{ code }}&room_id={{ room_id }}">
            <span>我的CP</span>
            <img class="cp_arrow" :src="cp_arrow" alt="">
        </a>
    {% endif %}
</div>
<script>
    var opts = {
        data: {
            sid: '{{ sid }}',
            code: '{{ code }}',
            cp_heart: '/m/images/cp_heart.png',
            cp_arrow: '/m/images/cp_arrow.png',
            room_host_user:{{ room_host_user }},
            pursuer:{{ pursuer }},
            current_user_id: "{{ current_user_id }}",
            room_id: "{{ room_id }}"

        },
        methods: {
            YseIDo: function () {
                var data = {
                    sid: vm.sid,
                    code: vm.code,
                    id: vm.current_user_id,
                    room_id: vm.room_id
                };

                $.authPost('/m/couples/create', data, function (resp) {
                    if (!resp.error_code) {
                        var url = '/m/couples/marriage?sid=' + vm.sid + '&code=' + vm.code + '&sponsor_id=' + resp.sponsor_id + '&pursuer_id=' + resp.pursuer_id;
                        window.location.href = url;
                    }
                    alert(resp.error_reason);
                });
            },
            getPursuer: function () {

                $.authGet('/m/couples/get_pursuer_user', {
                    room_id: vm.room_id,
                    sid: vm.sid,
                    code: vm.code
                }, function (resp) {
                    if (resp.pursuer) {
                        vm.pursuer = resp.pursuer;
                    } else {
                        setTimeout(function () {
                            vm.getPursuer();
                        }, 3000);
                    }
                })
            }
        }
    };

    vm = XVue(opts);
    $(function () {
        var is_show_alert = "{{ is_show_alert }}"
        if (is_show_alert) {
            alert('您还不在麦位上，再去争取机会啊！');
        }

        if (!vm.pursuer.uid) {

            setTimeout(function () {
                vm.getPursuer();
            }, 3000);

        }
    })
</script>
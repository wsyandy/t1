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
                <img :src="pursuer.avatar_url" alt="" @click="tab(1)">
                <span class="cp_name" v-text="pursuer.nickname">  </span>
            </div>
            <div class="cp_id">
                <span v-if="pursuer.uid">ID：</span>
                <span v-text="pursuer.uid"></span>
            </div>
        </div>
    </div>
    <!-- 其他弹框 -->
    <div class="room_btn" v-show="is_kick_out">
        <span class="room_out" @click="tab(0)">取消</span>
        <span class="room_in" @click="kickOut()">踢除此人</span>
    </div>
    <div class="cp_btn" :class="{'cp_btn_on':pursuer.uid}" @click="YseIDo()">
        <span>我愿意</span>
    </div>
    {% if is_host %}
        <a class="cp_my" href="/m/couples/my_couples?sid={{ sid }}&code={{ code }}&room_id={{ room_id }}">
            <span>我的CP</span>
            <img class="cp_arrow" :src="cp_arrow" alt="">
        </a>
    {% endif %}
</div>
<script>
    var opts = {
        data: {
            is_kick_out: false,
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
            tab: function (index) {
                switch (index) {
                    case 0:
                        vm.is_kick_out = false;
                        break;
                    case 1:
                        vm.is_kick_out = true;
                        break;
                }
            },
            YseIDo: function () {
                var data = {
                    sid: vm.sid,
                    code: vm.code,
                    room_host_user_id: vm.room_host_user.id,
                    pursuer_id: vm.pursuer.id,
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
            },
            kickOut: function () {
                vm.is_kick_out = false;
                var data = {
                    sid: vm.sid,
                    code: vm.code,
                    room_id: vm.room_id
                };

                $.authPost('/m/couples/kick_out', data, function (resp) {
                    alert(resp.error_reason);
                    location.reload(true);
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
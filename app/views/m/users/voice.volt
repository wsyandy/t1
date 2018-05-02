{{ block_begin('head') }}
{{ theme_css('/m/css/voice_main1.css') }}
{{ block_end() }}
<div id="app" style="flex-direction: column;min-height: 100%;justify-content: space-between;">
    <div class="sound_entry">
        <div class="sound_entry_input">
            <input type="text" :placeholder="nickname?nickname:'给自己起一个好听的名字'" v-model="nickname" maxlength="10"/>
        </div>
        <ul class="sound_entry_select">
            <li :class="['men',select_sex&&'selected_men']" @click="selectMale()"><span class="men_icon"></span>男神</li>
            <li :class="['women',!select_sex&&'selected_women']" @click="selectFemale()"><span
                        class="women_icon"></span>女神
            </li>
        </ul>
        <div class="sound_entry_button" @click="go_voice_identify()"><span>声音鉴定</span></div>
        <span class="sound_entry_wire"></span>
        <div class="sound_entry_logo">
            <img src="/m/images/logo2.png" alt="logo"/>
            <span>Hi语音鉴定，必属精品</span>
        </div>
    </div>
    <div class="sound_entry_bottom_bg"></div>
</div>
<script>
    var opts = {
        data: {
            user:{{ user }},
            select_sex: true,
            sex: 1,
            sid: "{{ sid }}",
            code: "{{ code }}",
            nickname: ""
        },
        methods: {
            go_voice_identify: function () {
                if (vm.nickname) {
                    var url = '/m/users/recording';
                    vm.redirectAction(url + '?sid=' + vm.sid + '&code=' + vm.code + '&sex=' + vm.sex + '&nickname=' + vm.nickname);
                } else {
                    alert('请输入昵称！');
                }

            },
            selectMale: function () {
                vm.select_sex = true;
                vm.sex = 1;
            },
            selectFemale: function () {
                vm.select_sex = false;
                vm.sex = 0;
            }
        }
    };
    vm = XVue(opts);
    $(function () {
        vm.sex = vm.user.sex;
        vm.nickname = vm.user.nickname;
        console.log(vm.nickname);
        if (vm.sex) {
            vm.select_sex = true;
        } else {
            vm.select_sex = false;
        }
    })
</script>
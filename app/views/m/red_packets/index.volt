{{ block_begin('head') }}
{{ theme_css('/m/css/red_packet_address.css','/m/css/red_packet_index.css','/m/css/red_packet_sex_select.css','/m/css/picker.css') }}
{{ theme_js('/m/js/picker.min.js','/m/js/font_rem.js') }}
{{ block_end() }}
<div id="app">
    <div class="give_red_bg">
        <div class="bottom_bg"></div>
        <div class="give_red_box">
            <div class="give_list">
                <div class="give_box">
                    <h3>红包个数</h3>
                    <div class="give_input">
                        <input type="number" name="" placeholder="" v-model="number">
                        <span>个</span>
                    </div>
                </div>
                <p>红包个数不少于5个,不多于100个</p>
            </div>
            <div class="give_list">
                <div class="give_box">
                    <h3>红包金额</h3>
                    <div class="give_input">
                        <input type="number" name="" placeholder="" v-model="amount">
                        <i class="zuan"></i>
                    </div>
                </div>
                <p v-model="diamond_num_limit">红包金额不低于${diamond_num_limit}钻</p>
            </div>
            <div class="give_list">
                <div class="give_box">
                    <h3>领取方式</h3>
                    <div class="give_input">
                        <div class="get_font get_style" @click="getStyle">${red_packet_type_cur}</div>
                        <i class="give_right"></i>
                    </div>
                </div>
            </div>
            <div v-show="type == 'nearby'">
                <div class="give_list">
                    <div class="give_box">
                        <h3>位置／范围</h3>
                        <div class="give_input">
                            <div class="get_font" id="picker2" v-model="nearby_distance">${nearby_distance}</div>
                            <i class="give_right"></i>
                        </div>
                    </div>
                </div>
                <div class="give_list">
                    <div class="give_box">
                        <h3>性别筛选</h3>
                        <div class="give_input">
                            <div class="get_font sex_btn" v-model="sex">${sex}</div>
                            <i class="give_right"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="give_number">
                <h5>钻石余额： <span>{{ diamond }}</span></h5>
                <a href="/m/products?sid={{ sid }}&code={{ code }}">充值 <i class="icon_right"></i></a>
            </div>
            <div class="give_btn" @click="sendRedPacket">
                <span>发红包</span>
            </div>
            <div class="give_text fang_give_text">
                <a href="/m/red_packets/state?sid={{ sid }}&code={{ code }}">红包说明 <i
                            class="icon_right"></i></a>
                <p>未领取的红包，将于24小时后退至我的帐户</p>
            </div>
        </div>
    </div>
    <!-- 领取方式弹框 -->
    <div class="get_style_bg" v-if="isGiveStyle">
        <div class="get_style_box">
            <ul>
                {% for index,val in red_packet_type %}
                    <li @click="selectType('{{ index }}','{{ val }}')">{{ val }} </li>
                {% endfor %}
                {#<li class="border_none">附近人才能领取</li>#}
            </ul>
            <div class="line"></div>
            <div class="quxiao" @click="cancel(1)">取消</div>
        </div>
    </div>
    <!-- 钻石不足弹框 -->
    <div v-if="less_zuan_input">
        <div class="zuan_less_box less_show">
            <i class="less_close" @click="cancel(2)"></i>
            <p>您的钻石余额不足，请充值后再发红包</p>
            <a href="/m/users/account?sid={{ sid }}&code={{ code }}" class="less_btn">去充值</a>
        </div>
    </div>
    <!-- 其他弹框 -->
    <div v-if="error_input">
        <div class="zuan_less_box less_show">
            <i class="less_close" @click="cancel(3)"></i>
            <p v-text="error_reason"></p>
            <a href="#" class="less_btn" @click="cancel(3)">取消</a>
        </div>
    </div>

    <!-- 性别弹出层 -->
    <div class="cover" v-if="isSex">
        <div class="popup_sex">
            <ul class="sec_tabs">
                <li v-for="(v,i) in allSex" @click="selectSex(i)">
                    <div class="ico_sex male"></div>
                    <span>${v}</span>
                </li>
            </ul>
            <a href="#" class="cancel">取消</a>
        </div>
    </div>
</div>
<script type="text/javascript">

    var opts = {
        data: {
            sid: "{{ sid }}",
            code: "{{ code }}",
            myDiamond: {{ diamond }},
            number: 5,
            amount: 100,
            nearby_distance: '5km',
            allSex: ['女生', '男生', '男女皆可'],
            sex: "男女皆可",
            red_packet_type_cur: '{{ red_packet_type['all'] }}',
            type: 'all',
            isGiveStyle: false,
            less_zuan_input: false,
            isSex: true,
            error_input: false,
            error_reason: '',
            diamond_num_limit:'100',
            submit: false
        },

        methods: {
            selectType: function (i, v) {
                this.red_packet_type_cur = v;
                this.type = i;
                if(this.type == "all"){
                    this.diamond_num_limit = "100";
                    this.amount = 100;
                    this.num = 5;
                }
                if(this.type == "follow" || this.type == "stay_at_room"){
                    this.diamond_num_limit = "1000";
                    this.amount = 1000;
                    this.num = 5;
                }
                if(this.type == "nearby"){
                    this.diamond_num_limit = "10000";
                    this.amount = 10000;
                    this.num = 5;
                }
                vm.isGiveStyle = false;

            },
            selectSex: function (i) {
                vm.isSex = true;
                this.sex = i;
            },
            getStyle: function () {
                vm.isGiveStyle = true;

            },
            openSex: function () {
                vm.isSex = true;
            },
            cancel: function (index) {
                switch (index) {
                    case 1:
                        vm.isGiveStyle = false;
                        break;
                    case 2:
                        vm.less_zuan_input = false;
                        break;
                    case 3:
                        vm.error_input = false;
                        break;
                }
            },
            sendRedPacket: function () {

                var data = {
                    sid: this.sid,
                    code: this.code,
                    num: this.number,
                    diamond: this.amount,
                    sex: this.sex,
                    red_packet_type: this.type,
                    nearby_distance: parseInt(this.nearby_distance) * 1000
                };

                if (this.sex == "男女皆可") {
                    data.sex = 2;
                }

                if (vm.amount > this.myDiamond) {
                    vm.less_zuan_input = true;
                    return;
                }

                if(this.submit){
                    return;
                }

                this.submit = true;

                $.authPost('/m/red_packets/create', data, function (resp) {
                    vm.submit = false;
                    vm.error_reason = resp.error_reason;
                    vm.error_input = true;
                    if (!resp.error_code) {
                        location.href = 'app://back';
                    }
                });


            }
        },

    };
    vm = XVue(opts);


    $(function () {
        // 领取方式弹框

        $('.get_style_box ul li').each(function () {
            $(this).click(function () {
                var textval = $(this).html();
                $('.get_style').html(textval);
                $('.get_style_bg').hide();
            })
        })

        $('.quxiao').click(function () {
            $('.get_style_bg').hide();
        })


        // 性别弹出层
        var $btn = $('.sex_btn');
        var $cover = $('.cover');
        var $secTabs = $('.sec_tabs').find("li");
        //打开窗口
        $btn.on('click', function (e) {
            e.preventDefault();
            $cover.addClass('is-visible');
        });
        //关闭窗口
        $cover.on('click', function (e) {
            /*点击确定按钮或者遮罩层关闭*/
            if ($(e.target).is('.cancel') || $(e.target).is('.cover')) {
                e.preventDefault();
                $(this).removeClass('is-visible');
            }
        });

        //选择
        $secTabs.on('click', function (e) {
//            e.preventDefault();
            $(this).addClass('select').siblings().removeClass('select')
        });

        $('.popup_sex ul li').each(function () {
            $(this).click(function () {
                var sexval = $(this).find('span').html();
                $('.sex_btn').html(sexval);
                $('.cover').removeClass('is-visible');
            })
        })

    });

    //距离弹出层
    var data1 = [
        {
            text: '附近',
            value: 1
        }
    ];

    var data2 = [
        {
            text: '5km',
            value: '5000'
        }, {
            text: '10km',
            value: '10000'
        },
        {
            text: '15km',
            value: '15000'
        },
        {
            text: '20km',
            value: '20000'
        },
        {
            text: '25km',
            value: '25000'
        },
        {
            text: '30km',
            value: '30000'
        },
        {
            text: '35km',
            value: '35000'
        }, {
            text: '40km',
            value: '40000'
        },
    ];

    var picker2El = document.getElementById('picker2');
    var picker2 = new Picker({
        data: [data1, data2]
    });
    picker2.on('picker.select', function (selectedVal, selectedIndex) {
        picker2El.innerText = data2[selectedIndex[1]].text;
        vm.nearby_distance = data2[selectedIndex[1]].text;
    });

    picker2.on('picker.change', function (index, selectedIndex) {
        //console.log(index);
    });

    picker2.on('picker.valuechange', function (selectedVal, selectedIndex) {
        //console.log(selectedVal);
    });

    picker2El.addEventListener('click', function () {
        picker2.show();
    });
</script>

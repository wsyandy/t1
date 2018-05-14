{{ block_begin('head') }}
{{ theme_css('/m/css/red_packet_address.css','/m/css/red_packet_index.css','/m/css/red_packet_sex_select.css') }}
{{ theme_js('/m/js/address.js','/m/js/font_rem.js') }}
{{ block_end() }}
<div id="app">
    <div class="give_red_bg">
        <div class="bottom_bg"></div>
        <div class="give_red_box">
            <div class="give_list">
                <div class="give_box">
                    <h3>红包个数</h3>
                    <div class="give_input">
                        <input type="text" name="" placeholder="number" v-model="number">
                        <span>个</span>
                    </div>
                </div>
                <p>红包个数不少于10个</p>
            </div>
            <div class="give_list">
                <div class="give_box">
                    <h3>红包金额</h3>
                    <div class="give_input">
                        <input type="text" name="" placeholder="amount" class="less_zuan_input" v-model="amount">
                        <i class="zuan"></i>
                    </div>
                </div>
                <p>红包金额不低于100钻</p>
            </div>
            <div class="give_list">
                <div class="give_box">
                    <h3>领取方式</h3>
                    <div class="give_input">
                        <div class="get_font get_style">都可以领取</div>
                        <i class="give_right"></i>
                    </div>
                </div>
            </div>
            <div class="give_list">
                <div class="give_box">
                    <h3>位置／范围</h3>
                    <div class="give_input">
                        <!-- <div class="get_font get_address">5km</div> -->
                        <input type="text" class="get_address" value="5km"/>
                        <i class="give_right"></i>
                    </div>
                </div>
            </div>
            <div class="give_list">
                <div class="give_box">
                    <h3>性别筛选</h3>
                    <div class="give_input">
                        <div class="get_font sex_btn">男女皆可</div>
                        <i class="give_right"></i>
                    </div>
                </div>
            </div>
            <div class="give_number">
                <h5>钻石余额： <span>{{ diamond }}</span></h5>
                <a href="/m/products?sid={{ sid }}&code={{ code }}">充值 <i class="icon_right"></i></a>
            </div>
            <div class="give_btn">
                <span>发红包</span>
            </div>
            <div class="give_text fang_give_text">
                <a href="/m/red_packet_histories/state?sid={{ sid }}&code={{ code }}">红包说明 <i
                            class="icon_right"></i></a>
                <p>未领取的红包，将于24小时后退至我的帐户</p>
            </div>
        </div>
    </div>
    <!-- 领取方式弹框 -->
    <div class="get_style_bg">
        <div class="get_style_box">
            <ul>
                <li>都可以领取</li>
                <li>关注房主才能领取</li>
                <li>在房间满3分钟才能领取</li>
                <li class="border_none">附近人才能领取</li>
            </ul>
            <div class="line"></div>
            <div class="quxiao">取消</div>
        </div>
    </div>
    <!-- 钻石不足弹框 -->
    <div class="get_zuan_less_bg">
        <div class="zuan_less_box less_show">
            <i class="less_close"></i>
            <p>您的钻石余额不足，请充值后再发红包</p>
            <a href="/m/products?sid={{ sid }}&code={{ code }}" class="less_btn">去充值</a>
        </div>
    </div>

    <!-- 性别弹出层 -->
    <div class="cover">
        <div class="popup_sex">
            <ul class="sec_tabs">
                <li>
                    <div class="ico_sex male"></div>
                    <span>男生</span>
                </li>
                <li>
                    <div class="ico_sex female"></div>
                    <span>女生</span>
                </li>
                <li>
                    <div class="ico_sex allsex"></div>
                    <span>男女皆可</span>
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
            number: 10,
            amount: 100
        },

        methods: {}
    };
    vm = XVue(opts);


    $(function () {
        // 领取方式弹框
        $('.get_style').click(function () {
            $('.get_style_bg').show();
        })

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

        // 金额不足弹框
        $('.less_zuan_input').click(function () {
            $('.get_zuan_less_bg').show();
        })
        $('.less_close').click(function () {
            $('.get_zuan_less_bg').hide();
        })

        // 距离选择
        // 弹出层数据
        var dataJson = [
            {
                "name": '附近',
                "value": '1',
                "child": [
                    {"name": '1km', "value": '1',},
                    {"name": '3km', "value": '3',},
                    {"name": '5km', "value": '5',},
                    {"name": '10km', "value": '10',},
                    {"name": '20km', "value": '20',},
                    {"name": '50km', "value": '50',}
                ]
            }
        ];
        /**
         * 联动的picker
         * 两级
         */
        $('.get_address').mPicker({
            level: 2,
            dataJson: dataJson,
            Linkage: true,
            rows: 6,
            idDefault: true,
            splitStr: '-',
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
</script>

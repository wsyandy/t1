{{ block_begin('head') }}
{{ weixin_css('voice_main.css') }}
{{ theme_js('/m/js/html2canvas.min') }}
{{ block_end() }}
<script src="/js/jweixin-1.0.0.js"></script>
<script src="/js/weixin_config.js"></script>
<script>
    (function (doc, win) {
        var docEl = doc.documentElement,
            resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
            recalc = function () {
                var clientWidth = docEl.clientWidth;
                if (!clientWidth) return;
                docEl.style.fontSize = 100 * (clientWidth / 750) + 'px';
            };

        if (!doc.addEventListener) return;
        win.addEventListener(resizeEvt, recalc, false);
        doc.addEventListener('DOMContentLoaded', recalc, false);
    })(document, window);
</script>
<div id="app" class="save_picture">
    <div :class="['save_picture_box',!sex&&'women']">
        <div class="save_picture_header" :style="{borderColor:!sex?'#F6427F':'#73B3FB'}">
            <img :src="avatar_url" alt="头像"/>
        </div>
        <div class="save_picture_name_box">
            <p class="save_picture_name">
                <span :style="{color:!sex?'#F53F7D':'#60A4F1',zIndex:1,position: 'relative'}">${nickname}</span>
                <span class="wire" :style="{backgroundColor:!sex?'#ffe2ec':'#d4e7fc'}"></span>
            </p>
        </div>
        <div class="save_picture_li">
            <span class="title">主音色:</span>
            <p class="save_picture_li_line">
                <span :style="{color:!sex?'#FF659A':'#71A7FC'}">${tonic}</span>
                <span :style="{color:!sex?'#FF659A':'#71A7FC'}">${tonic_ratio}%</span>
                <i :style="{backgroundColor:!sex?'#FF659A':'#71A7FC'}" class="wire"></i>
            </p>
        </div>
        <div class="save_picture_li">
            <span class="title">辅音色:</span>
            <div class="save_picture_libox">
                <p class="save_picture_li_line" style="margin-bottom:10px;"
                   v-for=" consonant1,consonant_ratio1 in consonant1">
                    <span :style="{color:!sex?'#FF659A':'#71A7FC'}">${consonant1}</span>
                    <span :style="{color:!sex?'#FF659A':'#71A7FC'}">${consonant_ratio1}%</span>
                    <i :style="{backgroundColor:!sex?'#FF659A':'#71A7FC'}" class="wire"></i>
                </p>
                <p class="save_picture_li_line" style="margin-bottom:10px;"
                   v-for=" consonant2,consonant_ratio2 in consonant2">
                    <span :style="{color:!sex?'#FF659A':'#71A7FC'}">${consonant2}</span>
                    <span :style="{color:!sex?'#FF659A':'#71A7FC'}">${consonant_ratio2}%</span>
                    <i :style="{backgroundColor:!sex?'#FF659A':'#71A7FC'}" class="wire"></i>
                </p>
                <p class="save_picture_li_line" style="margin-bottom:10px;"
                   v-for=" consonant3,consonant_ratio3 in consonant3">
                    <span :style="{color:!sex?'#FF659A':'#71A7FC'}">${consonant3}</span>
                    <span :style="{color:!sex?'#FF659A':'#71A7FC'}">${consonant_ratio3}%</span>
                    <i :style="{backgroundColor:!sex?'#FF659A':'#71A7FC'}" class="wire"></i>
                </p>
            </div>
        </div>
        <div class="save_picture_li">
            <div>
                <span class="title">攻受属性:</span>
                <span :style="{color:!sex?'#FF659A':'#71A7FC'}" class="text">${property}</span>
            </div>
            <div>
                <span class="title">推荐伴侣:</span>
                <span :style="{color:!sex?'#FF659A':'#71A7FC'}" class="text">${mate}</span>
            </div>
        </div>
        <div class="save_picture_li">
            <div>
                <span class="title">心动值:</span>
                <span :style="{color:!sex?'#FF659A':'#71A7FC'}" class="text">${heartbeat_value}</span>
            </div>
            <div>
                <span class="title">撩人值:</span>
                <span :style="{color:!sex?'#FF659A':'#71A7FC'}" class="text">${flirt_value}</span>
            </div>
            <div>
                <span class="title">扑倒值:</span>
                <span :style="{color:!sex?'#FF659A':'#71A7FC'}" class="text">${fall_down_value}</span>
            </div>
        </div>
        <div class="save_picture_li">
            <div>
                <span class="title">音色评价:</span>
                <span :class="[!sex?(grade?'score_icon3':'score_icon4'):(grade?'score_icon1':'score_icon2')]"></span>
            </div>
        </div>
        <div class="save_picture_bom">
            <div class="save_picture_bomleft">
                <div class="save_picture_bomleft_line">
                    <img src="/wx/yuewan/images/logo2.png" alt="logo">
                    <p>Hi语音</p>
                </div>
                <p class="hint">扫一扫，生成你的声鉴卡</p>
            </div>
            <div :class="['save_picture_qr_code',!sex&&'women']">
                <img src="/wx/yuewan/images/wx_m.jpg" alt="">
            </div>
        </div>
    </div>
    <div class="save_picture_fl" :style="{backgroundColor:!sex?'#FF659A':'#71A7FC'}">
        <div class="button" :style="{color:!sex?'#FF659A':'#71A7FC'}" @click="shareVoice('friend')"><span>分享朋友</span></div>
        <div class="button1" :style="{color:!sex?'#FF659A':'#71A7FC'}" @click="shareVoice('moments')"><span>分享朋友圈</span></div>
        <div class="button" :style="{color:!sex?'#FF659A':'#71A7FC'}" @click="go_voice_identify()"><span>重新鉴定</span>
        </div>
    </div>
    <div class="prompt_share" @click="hiddenAction">
        <span class="prompt_arrow"></span>
        <img src="/wx/{{ current_theme }}/images/prompt_pioter.png" alt="点击右上角分享">
    </div>
</div>
<script>
    var opts = {
        data: {
            sex:{{ sex }},//0为女1为男 主题切换  原本是0为男1为女 现在样式中已全部取反
            tonic: "",
            nickname: "{{ nickname }}",
            consonants: [],
            tonic_ratio: "",
            property: '',
            mate: '',
            heartbeat_value: '',
            flirt_value: '',
            fall_down_value: '',
            grade: '',
            consonant1: '',
            consonant2: '',
            consonant3: '',
            avatar_url: ''
        },

        methods: {
            go_voice_identify: function () {
                var url = '/wx/users/recording';
                vm.redirectAction(url + '?sex=' + vm.sex + '&nickname=' + vm.nickname);
            },
            hiddenAction:function () {
                $('.prompt_share').hide();
            },
            shareVoice:function (type) {
                var data = {
                    'sid': vm.sid,
                    'code': vm.code
                };

                $.authPost('/wx/users/get_image_for_wx_share', data, function (resp) {
                    if (0 == resp.error_code) {
                        switch (type){
                            case 'friend':
                                wxFriendShare(resp);
                                break;
                            case 'moments':
                                wxMomentsShare(resp);
                                break;
                        }
                    } else {
                        alert(resp.error_reason);
                    }
                });
                $('.prompt_share').show();
            }
        }
    };
    vm = XVue(opts);
    $(function () {
        getTonic();
    });

    function getTonic() {
        var data = {
            'sex': vm.sex
        };
        $.authGet('/wx/users/get_tonic', data, function (resp) {
            if (!resp.error_code) {
                vm.tonic = resp.tonic;
                vm.tonic_ratio = resp.tonic_ratio;
                if (resp.avatar_url) {
                    vm.avatar_url = resp.avatar_url;
                } else {
                    if (vm.sex) {
                        vm.avatar_url = '/wx/yuewan/images/men_haeder.png';
                    } else {
                        vm.avatar_url = '/wx/yuewan/images/women_haeder.png';
                    }
                }

                getConsonants();
                getProperty();
                getCharmValue();
            }
        })
    }

    function getConsonants() {
        var data = {
            'sex': vm.sex,
            'tonic_ratio': vm.tonic_ratio
        };
        $.authGet('/wx/users/get_consonants', data, function (resp) {
            if (!resp.error_code) {
                vm.consonant1 = resp.consonant1;
                vm.consonant2 = resp.consonant2;
                vm.consonant3 = resp.consonant3;
            }
        })
    }

    function getProperty() {
        var data = {
            'sex': vm.sex
        };
        $.authGet('/wx/users/get_property', data, function (resp) {
            if (!resp.error_code) {
                vm.property = resp.property;
                vm.mate = resp.mate;
            }
        })
    }

    function getCharmValue() {
        var data = {
            'sex': vm.sex
        };
        $.authGet('/wx/users/get_charm_value', data, function (resp) {
            if (!resp.error_code) {
                vm.heartbeat_value = resp.heartbeat_value;
                vm.flirt_value = resp.flirt_value;
                vm.fall_down_value = resp.fall_down_value;
                vm.grade = resp.grade;
            }
        })
    }

    var weixin_config_params = {
        debug: false,
        appId: "{{ sign_package["appId"] }}",
        timestamp: "{{ sign_package['timestamp'] }}",
        nonceStr: "{{ sign_package['nonceStr'] }}",
        signature: "{{ sign_package['signature'] }}",
        jsApiList: ["onMenuShareTimeline", "onMenuShareAppMessage", "onMenuShareQQ", "onMenuShareQZone"],
    };

    weixinJsConfig.initWxConfig(weixin_config_params);

    function wxFriendShare(resp) {
        wx.onMenuShareAppMessage({
            title: resp.title,
            desc: resp.description,
            link: resp.link,
            imgUrl: resp.image_url,
            success: function () {
                $('.prompt_share').hide();
            }
        });
    }

    function wxMomentsShare(resp) {
        wx.onMenuShareTimeline({
            title: resp.title,
            link: resp.link,
            imgUrl: resp.image_url,
            success: function () {
                $('.prompt_share').hide();
            }
        })
    }

</script>
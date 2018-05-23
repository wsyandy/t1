{{ block_begin('head') }}
{{ theme_css('/m/css/cp_apple.css','/m/css/cp_certificate.css') }}
{{ theme_js('/m/js/cp_resize.js','/m/js/html2canvas.min') }}
{{ block_end() }}
<div class="vueBox" id="app">
    <div class="save_picture_box">
        <div class="cer_bg">
            <div class="cer_head">
                <ul class="cer_text">
                    <li>
                        <span>持证人</span>
                        <span class="cer_holder">${sponsor.nickname}</span>
                    </li>
                    <li>
                        <span>持证人</span>
                        <span class="cer_holder">${pursuer.nickname}</span>
                    </li>
                </ul>
                <div class="cer_imgs">
                    <div class="cer_avatar">
                        <img :src="sponsor.avatar_url" alt="" crossorigin="anonymous">
                    </div>
                    <img class="cer_heart" :src="cer_heart" alt="">
                    <div class="cer_avatar">
                        <img :src="pursuer.avatar_url" alt="" crossorigin="anonymous">
                    </div>
                </div>
            </div>

            <ul class="cer_info">
                <li>
                    <span>发证机关</span>
                    <span class="cer_font">Hi民政局</span>
                </li>
                <li>
                    <span>登记日期</span>
                    <span class="cer_font">${marriage_at_text}</span>
                </li>
            </ul>
        </div>
        <div class="qr_box">
            <div class="qr_left">
                <div class="logo_box">
                    <img class="logo" :src="logo" alt="">
                    <span>Hi语音</span>
                </div>
                <div class="qr_left_scan">
                    扫一扫，祝福这对情侣
                </div>
            </div>
            <div class="qr_bg">
                <img class="qr_code" :src="qr_code" alt="">
            </div>
        </div>
    </div>
    <div class="height1rem"></div>
    <div class="cer_foot">
        <div class="cer_btn">
            <div class="save_image" @click="screenshotsImg('save')"> 存至相册</div>
            <div class="cp_relieve" @click="cpRelieve" v-if="is_show"> 解除 CP</div>
        </div>
        <img class="cer_share" :src="cer_share" alt="" @click="cerShare">
    </div>
    <!--解除CP弹出层-->
    <div class="mask" :class="{'is_visible':isrelieve}">
        <div class="relieve_cp" v-if="showrelieve">
            <div class="relieve_tips">
                <span>确认解除情侣后情侣值将清空，并移除您与${pursuer.nickname}在情侣值排行榜中的排名</span>
            </div>
            <ul class="relieve_btn">
                <li @click="cancelRelieve(0)"><span>取消</span></li>
                <li @click="cancelRelieve(1)"><span>确定</span></li>
            </ul>
        </div>
        <div class="toast_tips" v-show="istoast">
            <span v-text="error_reason"></span>
        </div>
    </div>

    <div class="mask" :class="{'is_visible':is_visible}">
        <div class="share_box">
            <ul class="share_list">
                <li v-for="(item,index) in shareList" @click="shareTo(index)">
                    <img class="ico_share" :src="item.ico" alt="">
                    <span v-text="item.txt"> </span>
                </li>
            </ul>
            <div class="cancel" @click="cancelShare">取消</div>
        </div>
    </div>
    <div v-if="isShareSuccess" class="toast_text_box">
        <span class="toast_text">请稍后。。。</span>
    </div>
</div>
<script>
    var opts = {
        data: {
            isShareSuccess: false,
            is_show: false,
            sid: '{{ sid }}',
            code: '{{ code }}',
            logo: '/m/images/logo_2.png',
            cer_heart: '/m/images/cer_heart.png',
            qr_code: '/m/images/cp_qr_code.jpg',
            cer_share: '/m/images/cer_share.png',
            sponsor: {{ sponsor }},
            pursuer:{{ pursuer }},
            is_visible: false,
            isrelieve: false, /*解除CP遮罩层显示隐藏*/
            showrelieve: false,
            istoast: false,
            error_reason: '',
            shareList: [
                {
                    ico: '/m/images/ico_wechat.png',
                    txt: '微信好友'
                },
                {
                    ico: '/m/images/ico_friends.png',
                    txt: '朋友圈'
                },
                {
                    ico: '/m/images/ico_qq.png',
                    txt: 'QQ好友'
                },
                {
                    ico: '/m/images/ico_qqzone.png',
                    txt: 'QQ空间'
                },
                {
                    ico: '/m/images/ico_sina.png',
                    txt: '微博'
                },
            ],
            marriage_at_text: "{{ marriage_at_text }}"

        },
        methods: {
            /*解除CP遮罩层显示隐藏*/
            cpRelieve: function () {
                this.istoast = false;
                this.isrelieve = true;
                this.showrelieve = true;
            },
            cancelRelieve: function (i) {
                if (i) {
                    this.showrelieve = false;
                    this.istoast = true;
                    var data = {
                        sid: vm.sid,
                        code: vm.code,
                        first_user_id: vm.sponsor.id,
                        second_user_id: vm.pursuer.id

                    };
                    $.authPost('/m/couples/relieve_couple', data, function (resp) {
                        vm.error_reason = resp.error_reason;
                        setTimeout(function () {
                            vm.isrelieve = false
                            history.go(-1);
                        }, 1000);
                    });

                } else {
                    this.isrelieve = false
                }

            },
            cerShare: function () {
                this.is_visible = true
            },
            cancelShare: function () {
                this.is_visible = false
            },
            shareTo: function (index) {

                switch (index) {
                    case 0:
                        vm.share('wx_friend', 'image', 'cp_marriage', 'share_image');
                        break;
                    case 1:
                        vm.share('wx_moments', 'image', 'cp_marriage', 'share_image');
                        break;
                    case 2:
                        vm.share('qq_friend', 'image', 'cp_marriage', 'share_image');
                        break;
                    case 3:
                        vm.share('qq_zone', 'image', 'cp_marriage', 'share_image');
                        break;
                    case 4:
                        vm.share('sinaweibo', 'image', 'cp_marriage', 'share_image');
                        break;
                }
            },
            screenshotsImg: function (type) {
                html2canvas(document.querySelector(".save_picture_box"), {
                    backgroundColor: 'transparent',// 设置背景透明
                    useCORS: true
                }).then(function (canvas) {
                    canvasTurnImg(canvas, type)
                });
            },
            //platform => qq_friend：qq好友    qq_zone：qq空间    wx_friend：微信好友  wx_moments：朋友圈  sinaweibo：新浪微博
            //type => image：图片    web_page：网页   text：文本
            share: function (platform, type, share_source, action) {
                html2canvas(document.querySelector(".save_picture_box"), {
                    backgroundColor: 'transparent',// 设置背景透明
                    useCORS: true
                }).then(function (canvas) {
                    var image_data = canvasTurnImg(canvas, type)
                    var data = {
                        code: vm.code,
                        sid: vm.sid,
                        platform: platform,
                        type: type,
                        share_source: share_source,
                        image_data: image_data,
                        action: action
                    };

                    $.authPost('/m/shares/create', data, function (resp) {
                        vm.isShareSuccess = true;
                        setTimeout(function () {
                            vm.isShareSuccess = false;
                        }, 3000);
                        vm.redirect_url = resp.test_url;
                        console.log(vm.redirect_url);
                        location.href = vm.redirect_url;
                        vm.is_visible = false
                    })
                });
            }
        }
    };

    function canvasTurnImg(canvas, event_type) {
        // 图片导出为 png 格式
        var type = 'png';
        var imgData = canvas.toDataURL(type);

        switch (event_type) {
            case 'save':
                saveImage(imgData);
                break;
            case 'image':
                return imgData;
                break;
        }

    }

    function saveImage(img_data) {
        var file_type = 'base64';

        var params = {data: img_data, file_type: file_type};
        params = JSON.stringify(params)

        if ($.isIos()) {
            window.webkit.messageHandlers.saveImage.postMessage(params);
        } else {
            JsCallback.saveImageBase64(img_data);  //保存图片
        }
        alert('保存成功');
    }

    vm = XVue(opts);
</script>

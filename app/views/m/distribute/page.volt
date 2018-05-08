{{ block_begin('head') }}
{{ theme_css('/m/css/distribute_apple.css','/m/css/distribute_extend.css') }}
{{ theme_js('/m/js/resize.js','/m/js/html2canvas.min') }}
{{ block_end() }}
<div id="app">
    <div class="extend_box">
        <img class="extend_img" src="/m/images/extend_img.png" alt="">
        <img class="qr_code" :src="qrcode" alt="">
        <div class="extend_qr"> 识别图中二维码，加入{{ product_channel_name }}</div>
    </div>

    <ul class="extend_share">
        <li>
            <img src="/m/images/share_weiChat.png" alt="" @click="share('wx_friend','image','distribute')">
            <span>微信好友</span>
        </li>
        <li>
            <img src="/m/images/share_weiChatCircle.png" alt="" @click="share('wx_moments','image','distribute')">
            <span>朋友圈</span>
        </li>
        <li>
            <img src="/m/images/share_qq.png" alt="" @click="share('qq_friend','image','distribute')">
            <span>QQ好友</span>
        </li>
        <li>
            <img src="/m/images/share_sina.png" alt="" @click="share('sinaweibo','image','distribute')">
            <span>微博</span>
        </li>
        <li @click="screenshotsImg('save')">
            <img src="/m/images/share_download.png" alt="">
            <span>存至相册</span>
        </li>
    </ul>
    <div v-if="isShareSuccess" class="toast_text_box">
        <span class="toast_text">请稍后。。。</span>
    </div>
</div>
<script>
    var opts = {
        data: {
            qrcode: "{{ qrcode }}",
            isShareSuccess: false,
            sid: "{{ sid }}",
            code: "{{ code }}"
        },

        methods: {
            screenshotsImg: function (type) {
                html2canvas(document.querySelector(".extend_box"), {
                    backgroundColor: 'transparent',// 设置背景透明
                    useCORS: true
                }).then(function (canvas) {
                    canvasTurnImg(canvas, type)
                });
            },
            share: function (platform, type, share_source) {
                vm.isShareSuccess = true;
                html2canvas(document.querySelector(".extend_box"), {
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
                        image_data: image_data
                    };

                    $.authPost('/m/shares/create', data, function (resp) {
                        vm.isShareSuccess = false;
                        vm.redirect_url = resp.test_url;
                        console.log(vm.redirect_url);
                        location.href = vm.redirect_url;
                    })
                });
            }
        }
    };
    vm = XVue(opts);

    function canvasTurnImg(canvas, event_type) {
        var type = 'png';
        var img_data = canvas.toDataURL(type);

        switch (event_type) {
            case 'save':
                saveImage(img_data);
                break;
            case 'image':
                return img_data;
                break;
        }

    }

    function saveImage(img_data) {
        var file_type = 'base64';

        var params = {data: img_data, file_type: file_type};
        params = JSON.stringify(params)

        if ($.isIos()) {
            alert('ios begin');
            window.webkit.messageHandlers.saveImage.postMessage(params);
            alert('ios end');
        } else {
            alert('Android begin');
            JsCallback.saveImageBase64(img_data);  //保存图片
            alert('Android end');
        }
    }
</script>
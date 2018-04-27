;

var weixinJsConfig = function () {

    var config = {};

    config.initWxConfig = function (opts) {
        if (undefined == opts) {
            alert("微信配置参数为空");
            return;
        }
        wx.config({
            debug: opts.debug,
            appId: opts.appId,
            timestamp: opts.timestamp,
            nonceStr: opts.nonceStr,
            signature: opts.signature,
            jsApiList: opts.jsApiList
        });
        wx.ready(function () {
            if (undefined != vm.is_share_page && vm.is_share_page) {
                config.share(vm.share_data);
            }
        });
    }

    config.chooseImage = function (opts) {

        if (undefined == opts) {
            alert("微信配置参数为空");
            return;
        }

        wx.chooseImage({
            count: 1,
            sizeType: ['compressed'],
            sourceType: ['album'],
            success: function (res) {
                images.localId = res.localIds;
                //上传图片
                wx.uploadImage({
                    localId: images.localId[0], // 需要上传的图片的本地ID，由chooseImage接口获得
                    isShowProgressTips: 1, // 默认为1，显示进度提示
                    success: function (res) {
                        opts.upload(res);
                    }
                });
            }
        });
    }

    // title: '', // 分享标题
    // desc: '', // 分享描述
    // link: '', // 分享链接
    // imgUrl: '', // 分享图标
    // success: function () {}
    // cancel: function () {}
    config.share = function (opts) {

        if (undefined == opts) {
            alert("微信配置参数为空");
            return;
        }

        wx.onMenuShareAppMessage(opts);
        wx.onMenuShareTimeline(opts);
        wx.onMenuShareQQ(opts);
        wx.onMenuShareQZone(opts);
    }
    return config;
}()

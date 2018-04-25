{{ block_begin('head') }}
{{ block_end() }}

<div id="app" v-cloak>
    <p @click="createSharesHistory('qq_friend','web_page')">分享qq好友 网页</p>
    <p @click="createSharesHistory('qq_friend','text')">分享qq好友 文本</p>
    <p @click="createSharesHistory('qq_friend','image')">分享qq好友 图片</p>

    <p @click="createSharesHistory('qq_zone','web_page')">分享qq空间 网页</p>
    <p @click="createSharesHistory('qq_zone','text')">分享qq空间 文本</p>
    <p @click="createSharesHistory('qq_zone','image')">分享qq空间 图片</p>

    <p @click="createSharesHistory('wx_friend','web_page')">分享微信好友 网页</p>
    <p @click="createSharesHistory('wx_friend','text')">分享微信好友 文本</p>
    <p @click="createSharesHistory('wx_friend','image')">分享微信好友 图片</p>


    <p @click="createSharesHistory('wx_moments','web_page')">分享微信朋友圈 网页</p>
    <p @click="createSharesHistory('wx_moments','text')">分享微信朋友圈 文本</p>
    <p @click="createSharesHistory('wx_moments','image')">分享微信朋友圈 图片</p>


    <p @click="createSharesHistory('sinaweibo','web_page')">分享新浪微博 网页</p>
    <p @click="createSharesHistory('sinaweibo','text')">分享新浪微博 文本</p>
    <p @click="createSharesHistory('sinaweibo','image')">分享新浪微博 图片</p>

    <p v-text="url"></p>
</div>


<script>
    var opts = {
        data: {
            url: ''
        },
        methods: {
            createSharesHistory: function (platform, type) {
                var data = {
                    code: '{{ code }}',
                    sid: '{{ sid }}',
                    platform: platform,
                    type: type
                };

                $.authGet('/m/shares/create', data, function (resp) {

                    vm.url = resp.test_url;

                    location.href = vm.url;
                })
            }
        }
    };

    vm = XVue(opts);
</script>
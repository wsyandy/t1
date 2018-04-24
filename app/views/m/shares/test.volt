{{ block_begin('head') }}
{{ block_end() }}

<div id="app" v-cloak>
    <p @click="createSharesHistory('qq_friend')">分享qq好友</p>
    <p @click="createSharesHistory('qq_zone')">分享qq空间</p>
    <p @click="createSharesHistory('wx_friend')">分享微信好友</p>
    <p @click="createSharesHistory('wx_moments')">分享微信朋友圈</p>
    <p @click="createSharesHistory('sinaweibo')">分享新浪微博</p>


    <p v-text="url"></p>
</div>


<script>
    var opts = {
        data: {
            url: ''
        },
        methods: {
            createSharesHistory: function (platform) {
                var data = {
                    code: '{{ code }}',
                    sid: '{{ sid }}'
                };

                $.authGet('/m/shares/create', data, function (resp) {

                    vm.url = "app://share?platform=" + platform + "&title=" + resp.title + "&content=" + resp.description +
                            "&share_url=" + resp.url + "&icon=" + resp.image_url + "&share_history_id=" + resp.share_history_id;

                    location.href = vm.url;
                })
            }
        }
    };

    vm = XVue(opts);
</script>
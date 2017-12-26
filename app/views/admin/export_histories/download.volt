请再本页等待下载

<script type="text/javascript">

    var download = setInterval(refresh, 1000);

    function refresh() {
        $.post("/admin/export_histories/download", {'id':{{ id }}}, function (resp) {
            if (resp.redirect_url) {
                clearInterval(download);
                location.href = resp.redirect_url;
            }
        })
    }

</script>
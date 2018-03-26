;

$("#download_simulator_apk").click(function (e) {

    e.preventDefault();

    $.authGet('/web/home/simulator_apk', function (resp) {
        if (resp.error_url) {
            location.href = resp.error_url;
        }
    })
})
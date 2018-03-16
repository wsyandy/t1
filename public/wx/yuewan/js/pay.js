var js_api_parameters;
var runFlag = false;
var redirect_url;
function onBridgeReady() {
    if (runFlag || null === js_api_parameters) {
        return;
    }
    runFlag = true;
    WeixinJSBridge.invoke('getBrandWCPayRequest', js_api_parameters, function (res) {
        runFlag = false;
        // alert(JSON.stringify(js_api_parameters));
        // alert(JSON.stringify(res));
        // WeixinJSBridge.log(res.err_msg);
        // $("#pay_success_form").submit();
        paySuccess();
    });
}

function wxPay() {

    if (typeof WeixinJSBridge == "undefined") {
        if (document.addEventListener) {
            document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
        } else if (document.attachEvent) {
            document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
            document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
        }
    } else {
        onBridgeReady();
    }
}
function paySuccess(){
    location.href = redirect_url;
}
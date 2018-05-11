/**
 * YDUI 可伸缩布局方案
 * rem计算方式：设计图尺寸px / 100 = 实际rem  例: 100px = 1rem
 * 这个文件没有使用
 */
// 这个文件没有使用
!function (window) {
    // 这个文件没有使用
    /* 设计图文档宽度 */
    var docWidth = 750;
// 这个文件没有使用
    var doc = window.document,
        docEl = doc.documentElement,
        resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize';
// 这个文件没有使用
    var recalc = (function refreshRem () {
        var clientWidth = docEl.getBoundingClientRect().width;
// 这个文件没有使用
        /* 8.55：小于320px不再缩小，11.2：大于420px不再放大 */
        docEl.style.fontSize = Math.max(Math.min(20 * (clientWidth / docWidth), 11.2), 8.55) * 5 + 'px';
// 这个文件没有使用
        return refreshRem;
    })();
// 这个文件没有使用
    /* 添加倍屏标识，安卓倍屏为1 */
    docEl.setAttribute('data-dpr', window.navigator.appVersion.match(/iphone/gi) ? window.devicePixelRatio : 1);
// 这个文件没有使用
    if (/iP(hone|od|ad)/.test(window.navigator.userAgent)) {
        /* 添加IOS标识 */
        doc.documentElement.classList.add('ios');
        /* IOS8以上给html添加hairline样式，以便特殊处理 */
        if (parseInt(window.navigator.appVersion.match(/OS (\d+)_(\d+)_?(\d+)?/)[1], 10) >= 8)
            doc.documentElement.classList.add('hairline');
    }
// 这个文件没有使用
    if (!doc.addEventListener) return;
    window.addEventListener(resizeEvt, recalc, false);
    doc.addEventListener('DOMContentLoaded', recalc, false);
// 这个文件没有使用
}(window);
// 这个文件没有使用

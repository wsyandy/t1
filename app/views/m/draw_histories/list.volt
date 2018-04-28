{{ block_begin('head') }}
    {{ theme_css('/m/css/draw_histories.css') }}
{{ block_end() }}
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
<div id="app" class="winning_record">
    <div class="winning_record_number">
        <div class="winning_record_numberli">
            <span>钻石</span>
            <b>1000</b>
            <span class="wire"></span>
        </div>
        <div class="winning_record_numberli">
            <span>金币</span>
            <b style="color: #F6B92A;">1000</b>
        </div>
    </div>
    <div class="winning_record_list">
        <p class="title">明细</p>
        <ul class="winning_record_ul">
            <li>
                <div class="winning_record_ul_left">
                    <p>获得砖石</p>
                    <span>2018-04-25 12:23</span>
                </div>
                <div class="winning_record_ul_right">
                    <span>＋10</span>
                    <span class="diamond"></span>
                </div>
            </li>
            <li>
                <div class="winning_record_ul_left">
                    <p>获得砖石</p>
                    <span>2018-04-25 12:23</span>
                </div>
                <div class="winning_record_ul_right">
                    <span>＋10</span>
                    <span class="diamond"></span>
                </div>
            </li>
            <li>
                <div class="winning_record_ul_left">
                    <p>获得砖石</p>
                    <span>2018-04-25 12:23</span>
                </div>
                <div class="winning_record_ul_right">
                    <span>＋10</span>
                    <span class="gold"></span>
                </div>
            </li>
            <li>
                <div class="winning_record_ul_left">
                    <p>获得砖石</p>
                    <span>2018-04-25 12:23</span>
                </div>
                <div class="winning_record_ul_right">
                    <span>＋10</span>
                    <span class="gold"></span>
                </div>
            </li>
        </ul>
    </div>
</div>
<script src="/js/vue/2.0.5/vue.min.js"></script>
<script>
    var app = new Vue({
        el: '#app',
        data: {},
        methods: {}
    })
</script>

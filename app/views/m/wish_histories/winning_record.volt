{{ block_begin('head') }}
{{ theme_css('/m/css/winning_record.css') }}
{{ block_end() }}
<script>
        (function(doc, win) {
            var docEl = doc.documentElement,
                resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
                recalc = function() {
                    var clientWidth = docEl.clientWidth;
                    if (!clientWidth) return;
                    docEl.style.fontSize = 100 * (clientWidth / 750) + 'px';
                };

            if (!doc.addEventListener) return;
            win.addEventListener(resizeEvt, recalc, false);
            doc.addEventListener('DOMContentLoaded', recalc, false);
        })(document, window);
    </script>
<div id="app" class="winning_record">、
    <div class="wishing_rules wishing_record wishing_back" onclick="javascrtpt:history.back(-1);">
        <span>返回上级</span>
    </div>
    <ul class="winning_record_ul">
        <li v-for="lucky_name in lucky_names">
            <img src="" alt="">
            <div class="winning_record_box">
                <span>${ lucky_name.nickname } ID:${ lucky_name.uid }</span>
                <p>获得 <span> 价值3000元机械手表</span></p>
            </div>
        </li>
    </ul>
    <p class="record_qq">中奖用户请联系客服QQ：3407150190</p>
</div>
<script>
    var opts = {
        data: {
            sid: "{{ sid }}",
            code: "{{ code }}",
            lucky_names:{{ lucky_names }}
        },

        methods: {}
    };
    vm = XVue(opts);
</script>
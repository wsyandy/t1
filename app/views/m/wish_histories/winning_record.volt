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
<body>
<div class="wishing_rules wishing_record wishing_back" onclick="javascrtpt:history.back(-1);">
    <span>返回上级</span>
</div>
<div id="app" class="winning_record">
    <ul class="winning_record_ul">
        {% for val in lucky_names %}
        <li>
            <img src="" alt="">
            <div class="winning_record_box">
                <span>{{ val }}</span>
                <p>获得 <span> 3000元机械手表</span></p>
            </div>
        </li>
        {% endfor %}
    </ul>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {

        },
        mounted:function(){

        },
        methods: {

        }
    })
</script>
</body>
</html>
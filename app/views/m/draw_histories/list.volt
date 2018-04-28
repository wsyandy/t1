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
            <b>{{ diamond_total_number }}</b>
            <span class="wire"></span>
        </div>
        <div class="winning_record_numberli">
            <span>金币</span>
            <b style="color: #F6B92A;">{{ gold_total_number }}</b>
        </div>
    </div>
    <div class="winning_record_list">
        <p class="title">明细</p>
        <ul class="winning_record_ul">
            <li v-for="draw_history in draw_histories">
                <div class="winning_record_ul_left">
                    <p>获得${draw_history.type_text}</p>
                    <span>${draw_history.created_at_text}</span>
                </div>
                <div class="winning_record_ul_right">
                    <span>＋${draw_history.number}</span>
                    <span :class="{'diamond': draw_history.type =='diamond','gold': draw_history.type =='gold'}"></span>
                </div>
            </li>
        </ul>
    </div>
</div>
<script>
    var opts = {
        data: {
            sid: '{{ sid }}',
            code: '{{ code }}',
            draw_histories: [],
            page: 1,
            total_page: 1,
            loading: false
        },
        methods: {
            loadData: function () {

                if (vm.page > vm.total_page) {
                    return false;
                }

                vm.page++;

                var data = {
                    sid: this.sid,
                    code: this.code,
                    page: this.page
                };

                $.authGet('/m/draw_histories/list', data, function (resp) {
                    vm.total_page = resp.total_page;
                    vm.loading = false;

                    if (resp.draw_histories) {
                        $.each(resp.draw_histories, function (i, item) {
                            vm.draw_histories.push(item);
                        });
                    }
                });
            }
        }
    };

    var vm = new XVue(opts);

    vm.loadData();

    $(function () {
        $(window).scroll(function () {
            if ($(document).scrollTop() >= $(document).height() - $(window).height()) {
                if (vm.loading) {
                    return;
                }

                vm.loading = true;
                vm.loadData();
            }
        });
    })

</script>

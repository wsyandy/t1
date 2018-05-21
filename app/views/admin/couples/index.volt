<table class="table table-striped table-condensed" id="app">
    <thead>
    <tr>
        <th>时间</th>
        <th>发起者</th>
        <th>追求者</th>
        <th>情侣值</th>
    </tr>
    </thead>

    <tbody id="stat_list" v-for="couple in couples">
    <tr class="row_line">
        <td>${couple.cp_at_text}</td>
        <td>${couple.sponsor_nickname}</td>
        <td>${couple.pursuer_nickname}</td>
        <td>${couple.score?couple.score:0}</td>
    </tr>
    </tbody>
</table>
<script type="text/javascript">
    var opts = {
        data: {
            couples: [],
            page: 1

        },
        created: function () {
            this.getCouples();
        },
        methods: {
            getCouples: function () {
                var data = {
                    page: this.page
                };
                $.authPost('/admin/couples', data, function (resp) {
                    if (!resp.error_code && resp.couples) {
                        $.each(resp.couples, function (index, couple) {
                            vm.couples.push(couple);
                        });
                        vm.page++;
                    }
                })
            }
        }
    };

    vm = XVue(opts);
    $(function () {
        $(document).scroll(
            function () {
                if ($(document).scrollTop() + window.innerHeight == $(document).height()) {
                    vm.getCouples()
                }
            });
    })
</script>


<div class="leftnav">
    <div class="leftnav-title">
        <div class="fadein-top">
            <div class="avatar">
                <img src="{{ current_user.avatar_small_url }}" class="radius-circle rotate-hover" alt=""/>
            </div>
            <div class="union_name">{{ union.name }}</div>
            <a class="logout" href="/partner/unions/logout">退出</a>
        </div>
    </div>
    <ul>
        <li><a href="/partner/unions/users" target="right" class="on"> 公会成员</a></li>
        <li><a href="/partner/unions/rooms" target="right" class="account">公会房间</a></li>
        <li><a href="/partner/unions/income_details" target="right" class="account">流水明细</a></li>
        <li><a href="/partner/unions/withdraw_histories" target="right" class="settle">结算明细</a></li>
    </ul>
</div>

<div class="admin">
    <iframe scrolling="auto" rameborder="0" src="" name="right" width="100%" height="100%"></iframe>
</div>

<script type="text/javascript">

    var opts = {
        data: {},
        methods: {}
    };

    var vm = XVue(opts);

    $(function () {
        $(".leftnav h2").click(function () {
            $(this).next().slideToggle(200);
            $(this).toggleClass("on");
        })
        $(".leftnav ul li a").click(function () {
            $("#a_leader_txt").text($(this).text());
            $(".leftnav ul li a").removeClass("on");
            $(this).addClass("on");
        })
    });
</script>

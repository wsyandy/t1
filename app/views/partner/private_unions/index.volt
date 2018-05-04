<div class="leftnav">
    <div class="leftnav-title">
        <div class="fadein-top">
            <div class="avatar">
                <img src="{{ current_user.avatar_small_url }}" class="radius-circle rotate-hover" alt=""/>
            </div>
            <div class="union_name">{{ union.name }}</div>
            <a class="logout" href="/partner/private_unions/logout">退出</a>
        </div>
    </div>
    <ul>
        <li><a href="/partner/private_unions/users" target="right" class="on">成员明细</a></li>
        <li><a href="/partner/private_unions/rooms" target="right" class="room">厅流水明细</a></li>
        {#<li><a href="/partner/private_unions/income_details" target="right" class="account">流水明细</a></li>#}
        {#<li><a href="/partner/private_unions/withdraw_histories" target="right" class="settle">结算明细</a></li>#}
    </ul>
</div>

<div class="admin">
    {% if 1 == union.status and 1 == union.auth_status %}
        <iframe scrolling="auto" rameborder="0" src="/partner/private_unions/users" name="right" width="100%"
                height="100%"></iframe>
    {% else %}
        <iframe scrolling="auto" rameborder="0" src="/partner/private_unions/auth_wait" name="right" width="100%"
                height="100%"></iframe>
    {% endif %}
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

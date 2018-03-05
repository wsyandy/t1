{#<script type="text/javascript" src="https://webapi.amap.com/maps?v=1.3&key=6fbcb9c9218e2a33cc548a9b2f60ab27"></script>#}


<ul class="nav nav-tabs" id="user_menus">
    <li role="presentation" class="active"><a href="/admin/users/avatar_info">全部</a></li>
    <li role="presentation"><a href="/admin/users/avatar_info?user_id={{ user_id }}&auth_status=3">待审核</a></li>
    <li role="presentation"><a href="/admin/users/avatar_info?user_id={{ user_id }}&auth_status=1">已审核通过</a></li>
    <li role="presentation"><a href="/admin/users/avatar_info?user_id={{ user_id }}&auth_status=2">审核失败</a></li>
    <li role="presentation"><a href="/admin/users/avatar_info?user_id={{ user_id }}&auth_status=1&auth_type=1">男头像</a></li>
    <li role="presentation"><a href="/admin/users/avatar_info?user_id={{ user_id }}&auth_status=1&auth_type=2">女头像</a></li>
    <li role="presentation"><a href="/admin/users/avatar_info?user_id={{ user_id }}&auth_status=1&auth_type=3">通用头像</a></li>
</ul>


<div id="user_content" class="ajax_content">

</div>
<script>

    $("#user_menus a").click(function (event) {
        event.preventDefault();

        $("#user_content").html();
        $(this).parents('ul').find('li').removeClass('active');

        $(this).parents("li").addClass('active');
        var link = $(this).attr('href');
        $.get(link, function (resp) {

            $("#user_content").html(resp);
        });
        return false;
    })
    $(".active a").trigger('click');

    $("#user_content").on('click', 'a', function (event) {
        if ($(this).hasClass('modal_action')) {
            return;
        }
        if ($(this).hasClass('once_click')) {
            return;
        }


        var link = $(this).attr('href');
        if ($(this).attr('target') == '_blank') {
            return;
        } else {
            event.preventDefault();
            $.get(link, function (resp) {

                $("#user_content").html(resp);
            });
        }
        return false;
    });
</script>
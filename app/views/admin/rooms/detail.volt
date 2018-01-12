<script type="text/javascript" src="https://webapi.amap.com/maps?v=1.3&key=6fbcb9c9218e2a33cc548a9b2f60ab27"></script>

<ul class="nav nav-tabs" id="room_menus">
    <li role="presentation" class="active"><a href="/admin/rooms/online_users?id={{ room.id }}">在线用户</a></li>
    <li role="presentation"><a href="/admin/rooms/room_seats?id={{ room.id }}">麦位</a> </li>
</ul>

<div id="room_content" class="ajax_content">

</div>
<script>

    $("#room_menus a").click(function (event) {
        event.preventDefault();

        $("#room_content").html();
        $(this).parents('ul').find('li').removeClass('active');

        $(this).parents("li").addClass('active');
        var link = $(this).attr('href');
        $.get(link, function (resp) {

            $("#room_content").html(resp);
        });
        return false;
    })
    $(".active a").trigger('click');

    $("#room_content").on('click', 'a', function (event) {
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

                $("#room_content").html(resp);
            });
        }
        return false;
    });
</script>
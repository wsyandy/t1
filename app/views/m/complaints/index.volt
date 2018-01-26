<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>举报</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/m/css/complaint.css">
    <script src="/js/jquery/1.11.2/jquery.min.js"></script>
    <script src="/js/utils.js"></script>
</head>
<body>
<div class="jubao_top">请告诉我举报理由：</div>
<div class="jubao_list">
    <ul>
        {% for key,value in complaint_types %}
            <li id="{{ key }}"><span>{{ value }}</span><i class="jb_select"></i></li>
        {% endfor %}
    </ul>
</div>
<div class="get_out_btn jubao_btn">
    <a id="create" class="account_btn">举报</a>
</div>
<script type="text/javascript">
    var complaint_type = '';
    $(function () {
        $('.jubao_list ul li').each(function () {
            $(this).click(function () {
                //改变class
                //$(this).addClass('jb_selected').siblings().removeClass('jb_selected');
                $(this).find('.jb_select').addClass('jb_selected');
                $(this).siblings().find('.jb_select').removeClass('jb_selected');
                //获取 complaint_type
                complaint_type = $(this).attr("id");
            })
        });

        //设置默认选项
        var first_li = $("ul li:eq(0)");
        first_li.find(".jb_select").addClass('jb_selected');
        first_li.siblings().find(".jb_select").removeClass('jb_selected');
        complaint_type = first_li.attr("id");
    });

    $("#create").click(function () {
        create();
    });

    function create() {
        var data = {
            sid: "{{ sid }}",
            code: "{{ code }}",
            room_id:{{ room_id }},
            user_id: "{{ user_id }}",
            complaint_type: complaint_type
        };
        $.authPost("complaints/create", data, function (resp) {
            alert(resp.error_reason);
            if (resp.error_code == 0 && resp.error_url) {
                location.href = resp.error_url;
            }
        })
    }
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>举报</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="/m/css/complaint.css">
</head>
<body>
<div class="jubao_top">请告诉我举报理由：</div>
<div class="jubao_list">
    <ul>
        {% for key,value in complaint_types %}
            <li id="{{ key }}">{{ value }}</li>
        {% endfor %}
    </ul>
</div>
<div class="get_out_btn jubao_btn">
    <a id="create" class="account_btn">举报</a>
</div>

<script src="/js/jquery/1.11.2/jquery.min.js"></script>
<script type="text/javascript">
    $(function () {
        $('.jubao_list ul li').each(function () {
            $(this).click(function () {
                //改变class
                $(this).addClass('jb_selected').siblings().removeClass('jb_selected');
                //获取 complaint_type
                var complaint_type = $(this).attr("id");
                //改变链接地址
                document.getElementById("create").href="create?sid={{ sid }}&code={{ code }}&room_id={{ room_id }}&user_id={{ user_id }}&complaint_type="+complaint_type;
            })
        });

        var error_reason = "{{ error_reason }}";
        if( error_reason )
        {
            alert(error_reason);
        }
        //设置默认选项
        $("ul li:eq(0)").addClass('jb_selected').siblings().removeClass('jb_selected');
        //设置默认链接
        document.getElementById("create").href="create?sid={{ sid }}&code={{ code }}&room_id={{ room_id }}&user_id={{ user_id }}&complaint_type="+$("ul li:eq(0)").attr("id");
    });
</script>
</body>
</html>

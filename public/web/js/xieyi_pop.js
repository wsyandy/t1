$(function(){
    
    function colse_fd(){
        $(".xy_fudong").hide();
        $(".xy_fudong_bg").hide();
    };

    $(".xy_fudong").hide();
    $(".xy_fudong_bg").hide();
    var doc_height=$(document).height();
    var w_height=$(window).height();
    var w_width=$(window).width();

    $(".xieyi_pop").click(function(){
        $(".xy_fudong").show();
        $(".xy_fudong_bg").show();

        $(".xy_fudong_bg").attr("style","height:"+doc_height+"px");
        var div_width=$(".xy_fudong").width();
        var div_height=$(".xy_fudong").height();

        var div_left=w_width/2-div_width/2+"px";
        var div_top = w_height/2 - div_height/2 + "px";

        $(".xy_fudong").css({
            "left":div_left,
            "top":div_top
        });
    })
    
    
    $(".close_btn").click(function(){
        colse_fd();
    });

});


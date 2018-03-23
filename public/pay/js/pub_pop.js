$(function(){
    
    function colse_fd(){
        $(".fudong").hide();
        $(".fudong_bg").hide();
    };
    
    $(".fudong").hide();
    $(".fudong_bg").hide();
    var doc_height=$(document).height();
    var w_height=$(window).height();
    var w_width=$(window).width();
    
    $(".upload_btn").click(function(){
        $(".fudong").show();
        $(".fudong_bg").show();

        $(".fudong_bg").attr("style","height:"+doc_height+"px");
        var div_width=$(".fudong").width();
        var div_height=$(".fudong").height();

        var div_left=w_width/2-div_width/2+"px";
        var div_top = w_height/2 - div_height/2 + "px";

        $(".fudong").css({
            "left":div_left,
            "top":div_top
        });
    })
    
    $(".close_btn").click(function(){
        colse_fd();
    });

});


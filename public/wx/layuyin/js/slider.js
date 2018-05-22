//学生案例
$(document).ready(function(){
	$(".students-share").hover(function(){
		$("#btn_prev,#btn_next").fadeIn()
	},function(){
		$("#btn_prev,#btn_next").fadeOut()
	});
	
	$dragBln = false;
	
	$(".box").touchSlider({
		flexible : true,
		speed : 200,
		btn_prev : $("#btn_prev"),
		btn_next : $("#btn_next"),
		paging : $(".flicking_con a"),
		paging : $(".flicking_li li"),
		counter : function (e){
			$(".flicking_con a").removeClass("on").eq(e.current-1).addClass("on");
		}
	});
	
	$(".box").bind("mousedown", function() {
		$dragBln = false;
	});
	
	$(".box").bind("dragstart", function() {
		$dragBln = true;
	});
	
	$(".box a").click(function(){
		if($dragBln) {
			return false;
		}
	});
	
	// timer = setInterval(function(){
	// 	$("#btn_next").click();
	// }, 5000);
	
	// $(".students-share").hover(function(){
	// 	clearInterval(timer);
	// },function(){
	// 	timer = setInterval(function(){
	// 		$("#btn_next").click();
	// 	},5000);                   
	// });
	
	// $(".box").bind("touchstart",function(){
	// 	clearInterval(timer);
	// }).bind("touchend", function(){
	// 	timer = setInterval(function(){
	// 		$("#btn_next").click();
	// 	}, 5000);
	// });
	
});
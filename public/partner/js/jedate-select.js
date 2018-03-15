/**
 * jeDate 调用
 */
//format: "YYYY-MM-DD hh:mm:ss"
$(function () {
    $("#timestart").jeDate({
        format: "YYYY-MM-DD"
    });

    $("#timesend").jeDate({
        format: "YYYY-MM-DD"
    });

    //常规选择
    /*  $("#search_year").jeDate({
          format: "YYYY"
      });
      */
    $("#search_month").jeDate({
        format: "YYYY-MM"
    });
});
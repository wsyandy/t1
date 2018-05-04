/**
 * jeDate 调用
 */
//format: "YYYY-MM-DD hh:mm:ss"
$(function () {
    $("#time_tart").jeDate({
        format: "YYYY-MM-DD"
    });

    $("#time_end").jeDate({
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
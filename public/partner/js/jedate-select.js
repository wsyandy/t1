/**
 * jeDate 调用
 */
$(function () {
    $("#timestart").jeDate({
        format: "YYYY-MM-DD hh:mm:ss"
    });
    $("#timesend").jeDate({
        format: "YYYY-MM-DD hh:mm:ss"
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
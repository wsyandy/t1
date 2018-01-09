;

var timer;

function getTaskState() {
    var url = '/admin/monitor/get_task_state';
    $.ajax({
        'url': url,
        'data': '',
        'type': 'GET',
        'dataType': 'html',
        'success': function (resp) {
            $("#task_container").html(resp);
        }
    });
}

getTaskState();

function refresh() {
    getTaskState();
    timer = setTimeout("refresh()", 3000);
}

$("body").on('click', '#autoRefreshSummary', function () {
    if (!$('#autoRefreshSummary').hasClass('btn-success')) {
        $('#autoRefreshSummary').toggleClass('btn-success');
        refresh();
    } else {
        clearTimeout(timer);
        $('#autoRefreshSummary').toggleClass('btn-success');
    }
});

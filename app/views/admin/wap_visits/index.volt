{% macro wap_visit_link(wap_visit) %}
    <a href="/admin/wap_visit_histories?wap_visit_id={{ wap_visit.id }}">IP分布</a>
{% endmacro %}

<form action="/admin/wap_visits" method="get" class="search_form" autocomplete="off">
    <label for="visit_at">时间</label>
    <input type="text" name="visit_at" class="form_datetime" id="visit_at" value="{{ visit_at }}" size="16">
    <label for="sem">
        sem
    </label>
    <select name="sem" id="sem" class="selectpicker" data-live-search="true">
        {{ options(sems, sem, 'sem', 'sem') }}
    </select>
    <button type="submit" class="ui button">搜索</button>
</form>
{{ simple_table(wap_visits,['ID':'id','时间':'visit_at_date','fr':'sem','落地页':'uri', '下载次数':'down_num', '访问次数':'visit_num','IP分布':'wap_visit_link']) }}

<script type="text/javascript">
    $(".form_datetime").datetimepicker({
        language: "zh-CN",
        format: 'yyyy-mm-dd',
        autoclose: 1,
        todayBtn: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2
    });
</script>
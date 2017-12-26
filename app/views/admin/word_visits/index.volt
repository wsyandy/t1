{% macro word_visit_link(word_visit) %}
    <a href="/admin/word_visit_histories?word_visit_id={{ word_visit.id }}">IP分布</a>
{% endmacro %}

<form action="/admin/word_visits" method="get" class="search_form" autocomplete="off">
    <label for="visit_at">时间从</label>
    <input type="text" name="visit_at" class="form_datetime" id="visit_at" value="{{ visit_at }}" size="16">
    <label for="visit_at_end">到</label>
    <input type="text" name="visit_at_end" class="form_datetime" id="visit_at_end" value="{{ visit_at_end }}" size="16">

    <label for="sem">
        sem
    </label>
    <select name="sem" id="sem" class="selectpicker" data-live-search="true">
        {{ options(sems, sem, 'sem', 'sem') }}
    </select>
    <label for="sem">
        是否导出
    </label>
    <select name="export" id="export">
        <option value="">否</option>
        <option value="1">导出</option>
    </select>
    <button type="submit" class="ui button">搜索</button>
</form>


{{ simple_table(word_visits,['ID':'id','时间':'visit_at_date', 'sem':'sem','下载次数':'down_num', '关键词':'word','访问次数':'visit_num',
'IP分布' : 'word_visit_link'
]) }}

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
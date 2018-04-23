{% set f = simple_form([ 'admin', room_category ], ['enctype': 'multipart/form-data', 'class':'ajax_model_form']) %}

{{ f.hidden('parent_id') }}

{{ f.input('name', [ 'label':'名称','width':'50%' ]) }}
{{ f.input('type',['label':'类型','width':'50%']) }}
{{ f.input('rank',['label':'排序', 'width':'50%']) }}
{{ f.select('status',['label':'状态', 'collection': Activities.STATUS, 'width':'50%']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}


<script type="text/javascript">
    $(function () {

        $(".form_datetime").datetimepicker({
            language: "zh-CN",
            format: 'yyyy-mm-dd hh:ii',
            autoclose: 1,
            todayBtn: 1,
            todayHighlight: 1,
            startView: 2,
        });
    });
</script>
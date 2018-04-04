{% set f = simple_form([ 'admin', activity ], ['enctype': 'multipart/form-data', 'class':'ajax_model_form']) %}

{{ f.input('title', [ 'label':'标题','width':'50%' ]) }}
{{ f.input('code', [ 'label':'code' ,'width':'50%']) }}
{{ f.file('image', ['label': '图片']) }}
{{ f.input('start_at',['label':'开始时间','class':'form_datetime','width':'50%']) }}
{{ f.input('end_at',['label':'结束时间','class':'form_datetime','width':'50%']) }}
{{ f.input('rank',['label':'排序', 'width':'50%']) }}
{{ f.select('status',['label':'状态', 'collection': Activities.STATUS, 'width':'50%']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}


<script type="text/javascript">
    $(function () {
        $(".form_datetime").datetimepicker({
            language: "zh-CN",
            format: 'yyyy-mm-dd hh:00',
            weekStart: 1,
            autoclose: 1,
            todayBtn: 1,
            todayHighlight: 1,
            startView: 2,
            minView: 0
        });
    });
</script>
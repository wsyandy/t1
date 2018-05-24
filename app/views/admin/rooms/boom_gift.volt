{% set f = simple_form('/admin/rooms/boom_gift?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'room']) %}

{{ f.input('url',['label':'跳转链接']) }}



{{ f.input('expire_at',['label':'结束时间','class':'form_datetime']) }}
{{ f.input('total_value',['label':'总值']) }}
{{ f.input('current_value',['label':'当前值']) }}
{{ f.input('svga_image_url', ['label': 'sva图片路径']) }}


<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

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


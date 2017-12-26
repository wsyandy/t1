{% set f = simple_form(['admin', weixin_template_message], ['class' : 'ajax_model_form']) %}
{{ f.select('product_channel_id', ['label':'产品渠道', 'collection':product_channels,'value_field':'id','text_field':'name','width':'50%']) }}
{{ f.input('name',['label':'名称', 'width':'50%']) }}
{{ f.select('status',['label':'是否有效','collection':WeixinTemplateMessages.STATUS, 'width':'50%']) }}
{{ f.input('offline_day',['label':'离线天数(7-15)','width':'50%']) }}
{{ f.input('send_at',['label':'发送时间','class':'form_datetime', 'width':'50%']) }}
{{ f.select('need_filter_conditions',['label':'是否需要过滤产品条件','collection':WeixinTemplateMessages.NEED_FILTER_CONDITIONS,'width':'50%']) }}
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
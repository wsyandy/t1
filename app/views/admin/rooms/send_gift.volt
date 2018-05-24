{% set f = simple_form('/admin/rooms/send_gift?user_id='~user_id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'room']) %}

{{ f.input('sender_id',['label':'用户ID']) }}
{{ f.input('gift_id',['label':'礼物ID']) }}


{{ f.input('left_pk_user_id',['label':'左边pk用户ID']) }}
{{ f.input('left_pk_user_score',['label':'左边pk用户分数']) }}
{{ f.input('right_pk_user_id',['label':'右边pk用户ID']) }}
{{ f.input('right_pk_user_score',['label':'右边pk用户分数']) }}

{{ f.input('expire_at',['label':'结束时间','class':'form_datetime']) }}
{{ f.input('total_value',['label':'总值']) }}
{{ f.input('current_value',['label':'当前值']) }}
{{ f.input('svga_image_url', ['label': 'sva图片路径']) }}

{{ f.input('content',['label':'消息内容']) }}
{{ f.input('title',['label':'标题']) }}
{{ f.select('content_type',['label':'消息内容类型','collection': content_types,'width':'100%']) }}

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


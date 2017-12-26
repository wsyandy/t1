<form action="/admin/sms_histories" method="get">
    <label for="product_channel">
        产品渠道
    </label>
    <select name="sms_history[product_channel_id_eq]" id="product_channel_id">
        {{ options(product_channels, 0, 'id', 'name') }}
    </select>

    手机号码:<input name="sms_history[mobile_eq]" type="text"/>

    <input type="submit" value="搜索" class="btn  btn-default">
</form>

{{ simple_table(sms_histories,[
'id': 'id','提交时间': 'created_at_text','发送渠道':'sms_channel_name','类型': 'sms_type','产品渠道':'product_channel_name',
'手机号码': 'mobile','内容': 'content','sms_token': 'sms_token','发送状态': 'send_status_text',
'验证状态': 'auth_status_text','更新时间': 'updated_at_text'
]) }}
<style>
    #column_content {
        width: 30%;
    }
</style>
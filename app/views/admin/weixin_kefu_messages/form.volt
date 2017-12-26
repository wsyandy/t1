{% set f = simple_form(['admin', weixin_kefu_message], ['class':'ajax_model_form']) %}
{{ f.select('product_channel_id',['label':'产品渠道','collection':product_channels,'value_field':'id','text_field':'name','width':'50%']) }}
{{ f.input('name', ['label':'名称','width':'50%']) }}
{{ f.select('status',['label':'状态','collection':WeixinKefuMessages.STATUS]) }}
{{ f.submit('保存') }}
{{ f.end }}
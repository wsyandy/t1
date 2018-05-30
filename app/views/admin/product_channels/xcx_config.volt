{% set f = simple_form(c('/admin/product_channels/update_xcx_config/',product_channel.id), product_channel,[ 'method': 'post' , 'class': 'ajax_model_form']) %}

{{ f.input('xcx_appid',['label':'appid']) }}
{{ f.input('xcx_secret',['label':'密钥']) }}
{{ f.input('xcx_domain',['label':'业务域名']) }}

{{ f.submit('保存') }}
<div class="error_reason" style="color:red"></div>
{{ f.end }}

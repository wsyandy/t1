{% set f = simple_form(c('/admin/product_channels/update_web_config/',product_channel.id), product_channel,[ 'method': 'post' , 'class': 'ajax_model_form']) %}

{{ f.input('web_name',['label':'名称']) }}
{{ f.input('web_domain',['label':'域名']) }}
{{ f.select('web_theme', ['label': '网页模板', 'collection': web_themes ]) }}

{{ f.input('web_fr',['label':'来源渠道']) }}
{{ f.submit('保存') }}
<div class="error_reason" style="color:red"></div>
{{ f.end }}
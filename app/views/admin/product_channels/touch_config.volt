{% set f = simple_form(c('/admin/product_channels/update_touch_config/',product_channel.id), product_channel,[ 'method': 'post' , 'class': 'ajax_model_form']) %}

{{ f.input('touch_name',['label':'名称']) }}
{{ f.input('touch_domain',['label':'域名']) }}
{{ f.select('touch_theme', ['label': '网页模板', 'collection': touch_themes ]) }}

{{ f.input('touch_fr',['label':'来源渠道']) }}
{{ f.submit('保存') }}
<div class="error_reason" style="color:red"></div>
{{ f.end }}
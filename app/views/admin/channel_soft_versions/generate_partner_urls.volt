{% set f = simple_form(c('/admin/channel_soft_versions/generate_partner_urls?id=', soft_version.id), soft_version, ['enctype': 'multipart/form-data', 'method':'POST', 'class':'ajax_model_form']) %}
{{ f.input('host',['label': '推广域名,配置对应的推广域名,默认(t.momoyuedu.cn)']) }}

<div class="error_reason" style="color: red"></div>

{{ f.submit('保存') }}

{{ f.end }}
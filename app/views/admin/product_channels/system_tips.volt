{% set f = simple_form(['admin',product_channel],['method':'post', 'class':'ajax_model_form']) %}
{{ f.input('system_tips',['label':'公告']) }}
<div class="error_reason" style="color: red;"></div>

{{ f.submit('保存') }}
{{ f.end }}
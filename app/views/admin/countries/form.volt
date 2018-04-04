{% set f = simple_form(['admin',country],['class':'ajax_model_form']) %}

{{ f.input('rank',['label':'排序','width': '50%']) }}
{{ f.select('status',['label':'状态', 'collection': Countries.STATUS,'width': '50%' ]) }}

{{ f.file('image',['label':'上传国旗']) }}
<div class="error_reason" style="color: red;"></div>

{{ f.submit('保存') }}
{{ f.end }}
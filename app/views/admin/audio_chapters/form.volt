{% set f = simple_form(['admin', audio_chapter], ['class': 'ajax_model_form', 'model': 'audio_chapter']) %}

{{ f.hidden('audio_id',['value':audio_id]) }}
{{ f.input('name',['label': '名称']) }}
{{ f.file('file',['label':'上传文件']) }}
{{ f.input('rank', ['label': '排名（不能重复）']) }}

{{ f.select('status', ['label': '状态', 'collection':Audios.STATUS]) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
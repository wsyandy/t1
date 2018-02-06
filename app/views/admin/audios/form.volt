{% set f = simple_form(['admin',audio],['class':'ajax_model_form']) %}

{{ f.input('name',['label': '名称']) }}
{{ f.input('rank', ['label': '排名（不能重复）']) }}
{{ f.select('audio_type',['label':'类型','collection':Audios.AUDIO_TYPE]) }}
{{ f.select('status', ['label': '状态', 'collection':Audios.STATUS]) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
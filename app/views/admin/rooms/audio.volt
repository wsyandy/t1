{% set f = simple_form('/admin/rooms/audio?id='~id, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'room']) %}

{{ f.select('audio_id', ['label': '音频ID', 'collection': audios,'text_field':'name','value_field':'id']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
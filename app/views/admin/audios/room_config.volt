{% set f = simple_form('/admin/audios/room_config?audio_id='~audio_id, ['method':'POST', 'class': 'ajax_model_form','data-model':'audio']) %}

{{ f.input('room_id',['label':'房间ID(为空则随机)']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('提交') }}

{{ f.end }}
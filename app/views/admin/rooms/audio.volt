{% set f = simple_form('/admin/rooms/audio?id='~id, room, ['method':'POST', 'class': 'ajax_model_form',
'data-model':'room']) %}
{{ f.select('theme_type',['label':'房间主题','collection':Rooms.THEME_TYPE]) }}
{{ f.select('audio_id', ['label': '音频ID', 'collection': audios]) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
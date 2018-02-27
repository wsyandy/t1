{% set f = simple_form(['admin', room_theme],['method':'post', 'class':'ajax_model_form', 'model': 'room_theme']) %}

{{ f.input('name',['label': '名称']) }}
{{ f.input('rank', ['label': '排名']) }}
{{ f.select('status', ['label': '状态', 'collection': RoomThemes.STATUS]) }}
{{ f.file('theme_image', ['label': '背景图']) }}
{{ f.file('icon', ['label': '图标']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}



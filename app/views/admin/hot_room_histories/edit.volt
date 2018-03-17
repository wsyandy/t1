{% set f = simple_form(['admin', hot_room_history],['method':'post', 'class':'ajax_model_form', 'model': 'hot_room_history',
'enctype': 'multipart/form-data']) %}

{{ f.select('status', ['label': '状态', 'collection': HotRoomHistories.STATUS]) }}
{#{{ f.input('error_reason', ['label': '原因(失败时填写)']) }}#}
{{ f.submit('保存') }}

<div class="error_reason" style="color:red;"></div>
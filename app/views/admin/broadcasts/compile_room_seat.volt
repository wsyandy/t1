{% set f = simple_form('/admin/broadcasts/compile_room_seat?seat_id='~seat_id,room_seat,['method':'POST','class':'ajax_model_form','data_modal':'room_seat']) %}
{{ f.select('status',['label':'麦位','collection':RoomSeats.STATUS]) }}
{{ f.select('microphone',['label':'麦克风','collection':microphone]) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit("提交") }}

{{ f.end }}
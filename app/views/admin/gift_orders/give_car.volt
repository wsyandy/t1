{% set f = simple_form('/admin/gift_orders/give_car?user_id='~user_id, ['class':'ajax_model_form', 'method':'post','data-model': 'union']) %}

{{ f.select('gift_id', ['label': '礼物', 'collection': gifts,'text_field':'name','value_field':'id','width': '100%']) }}
{{ f.input('content',['label':'赠送留言']) }}

{{ f.submit('保存') }}

<div style="color: red" class="error_reason"></div>
{{ f.end }}
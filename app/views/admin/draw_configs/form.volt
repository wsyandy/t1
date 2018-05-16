{% set f = simple_form(['admin',draw_config],['method':'post', 'class':'ajax_model_form']) %}
{#{{ f.hidden('product_channel_id') }}#}

{{ f.input('name', ['label': '名称', 'width':'100%']) }}
{{ f.input('rank', ['label': '排序', 'width':'50%']) }}
{{ f.input('type', ['label': '类型', 'width':'50%']) }}
{{ f.input('rate', ['label': '概率', 'width':'50%']) }}
{{ f.input('number', ['label': '数量', 'width':'50%']) }}
{{ f.input('day_limit_num', ['label': '每天限制数量', 'width':'50%']) }}
{{ f.input('gift_id', ['label': '礼物ID', 'width':'50%']) }}
{{ f.input('gift_num', ['label': '礼物数量', 'width':'50%']) }}
{{ f.select('status', ['label': '状态', 'collection': DrawConfigs.STATUS, 'width':'50%']) }}

<div class="error_reason" style="color: red;"></div>

{{ f.submit('保存') }}
{{ f.end }}
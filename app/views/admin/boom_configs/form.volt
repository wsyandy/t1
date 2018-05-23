{% set f = simple_form(['admin',boom_config],['class':'ajax_model_form']) %}

{{ f.input('name',['label': '名称']) }}
{{ f.input('rank', ['label': '排名']) }}
{{ f.input('start_value', ['label': '开始数值']) }}
{{ f.input('total_value', ['label': '总数值']) }}
{{ f.select('status', ['label': '状态', 'collection':BoomConfigs.STATUS]) }}
{{ f.file('svga_image', ['label': 'svga图']) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
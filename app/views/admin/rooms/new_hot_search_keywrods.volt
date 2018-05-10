{% set f = simple_form(c('/admin/rooms/new_hot_search_keywrods'), room,['method':'post', 'class':'ajax_model_form','data-model':'rooms']) %}
{{ f.input('keyword', [ 'label':'名称']) }}
{{ f.input('rank',['label':'排序']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}

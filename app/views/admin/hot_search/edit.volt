{% set f = simple_form(['admin', hot_search],['method':'post', 'class':'ajax_model_form', 'model':'hotSearch', 'data-model':'hotSearch', 'enctype': 'multipart/form-data']) %}

{{ f.input('word', ['label':'词名称', 'width':'100%']) }}
{{ f.input('weight', ['label':'权重', 'width':'100%']) }}

<div class="error_reason" style="color: red;"></div>

{{ f.submit('保存') }}
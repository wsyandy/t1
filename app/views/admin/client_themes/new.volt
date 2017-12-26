{% set f = simple_form(['admin',client_theme],['method':'post', 'class':'ajax_model_form']) %}
{{ f.hidden('product_channel_id') }}

{{ f.input('version_name',['label':'版本名称,例1.0.0','width':'33%']) }}
{{ f.input('version_code',['label':'版本code,整数,不能重复','width':'33%']) }}
{{ f.select('status', ['label': '状态', 'collection': ClientThemes.STATUS,'width': '33%']) }}

{{ f.select('platform', ['label': '手机平台', 'collection': SoftVersions.PLATFORM, 'width':'50%', 'blank':true]) }}
{{ f.input('soft_version_code',['label':'适用软件版本号(0:不限)','width':'50%']) }}

{{ f.file('file',['label':'主题文件']) }}
{{ f.textarea('remark',[ 'label':'更新简介' ]) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}
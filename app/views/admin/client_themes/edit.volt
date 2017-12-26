{% set f = simple_form(['admin',client_theme],['method':'post', 'class':'ajax_model_form']) %}
{{ f.hidden('product_channel_id') }}

{{ f.input('version_name',['label':'版本名称,例1.0.0','width':'33%', 'readonly':'true']) }}
{{ f.input('version_code',['label':'版本code,整数,不能重复','width':'33%', 'readonly':'true']) }}
{{ f.select('status', ['label': '状态', 'collection': ClientThemes.STATUS,'width': '33%']) }}

{{ f.input('ios_version_code',['label':'苹果-适用软件版本号(0:不限, -1:禁用)','width':'50%']) }}
{{ f.input('android_version_code',['label':'安卓-适用软件版本号(0:不限, -1:禁用)','width':'50%']) }}

{{ f.file('file',['label':'主题文件']) }}
{{ f.textarea('remark',[ 'label':'更新简介' ]) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}

<script type="text/javascript">
    $(function () {
        {% if  ios_version_code == -1 %}
        $('#client_theme_ios_version_code').attr('readonly', true);
        {% endif %}

        {% if android_version_code == -1 %}
        $('#client_theme_android_version_code').attr('readonly', true);
        {% endif %}
    });

</script>

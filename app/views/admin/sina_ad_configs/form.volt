{% set f = simple_form([ 'admin', sina_ad_config ], ['enctype': 'multipart/form-data', 'class':'ajax_model_form']) %}

{{ f.input('name',['label':'名称', 'width':'50%']) }}
{{ f.input('group_id',['label':'广告组ID', 'width':'50%']) }}

{{ f.input('convid',['label':'转化ID', 'width':'50%']) }}
{{ f.select('platform',['label':'平台','collection': SinaAdConfigs.PLATFORM, 'width':'50%']) }}

{{ f.input('token',['label':'TOKEN']) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}
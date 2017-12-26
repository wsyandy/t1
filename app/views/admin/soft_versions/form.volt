{% set f = simple_form([ 'admin', soft_version ], ['enctype': 'multipart/form-data', 'class':'ajax_model_form']) %}

{{ f.select('platform', ['label': '手机平台', 'collection': SoftVersions.PLATFORM, 'width':'50%', 'blank':true]) }}
{{ f.select('product_channel_id', ['label': '产品渠道', 'collection': product_channels,'text_field':'name','value_field':'id','width': '50%']) }}

{{ f.input('version_code',[ 'label':'版本号(version_code)', 'width':'50%' ]) }}
{{ f.input('version_name',[ 'label':'版本名称(version_name)', 'width':'50%' ]) }}
{{ f.select('status', ['label': '是否可用', 'collection': SoftVersions.STATUS, 'width':'50%', 'blank':true]) }}
{{ f.select('stable', ['label': '版本状态', 'collection': SoftVersions.STABLE, 'width':'50%', 'blank':true]) }}


{{ f.input('permit_ip',[ 'label':'仅以下IP可升级' ]) }}
{{ f.select('force_update', ['label': '是否强制升级', 'collection': SoftVersions.FORCE_UPDATE, 'blank':true,'width': '50%']) }}
{{ f.input('fr', ['label': '指定fr升级，空为全部适用','width': '50%']) }}

{{ f.input('built_in_fr', ['label': '安装包内置fr']) }}
{{ f.file('file',[ 'label':'上传安卓安装包' ]) }}
{{ f.input('ios_down_url',[ 'label':'IOS下载地址' ]) }}
{{ f.input('weixin_url',[ 'label':'应用宝下载地址' ]) }}

{{ f.textarea('feature',[ 'label':'更新简介' ]) }}
{{ f.input('remark',['label': '备注']) }}

<div class="error_reason" style="color: red"></div>
{{ f.submit('保存') }}

{{ f.end }}
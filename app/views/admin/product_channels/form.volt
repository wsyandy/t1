{% set f = simple_form(['admin',product_channel],['class':'ajax_model_form']) %}

{{ f.input('name',['label':'产品渠道名称','width': '30%']) }}
{{ f.input('code',['label':'code(禁止修改,禁止数字开头)','width': '40%']) }}
{{ f.select('status',['label':'状态', 'collection': ProductChannels.STATUS,'width': '30%' ]) }}

{{ f.input('company_name',['label':'公司名称', 'width': '50%']) }}
{{ f.input('service_phone',['label':'客服电话', 'width': '50%']) }}
{{ f.input('cooperation_weixin',['label':'合作微信', 'width': '50%']) }}
{{ f.input('cooperation_email',['label':'合作邮箱', 'width': '50%']) }}
{{ f.input('cooperation_phone_number',['label':'合作电话', 'width': '50%']) }}
{{ f.input('official_website',['label':'官方网站', 'width': '50%']) }}

{{ f.input('agreement_company_name',['label':'合同公司名称', 'width': '50%']) }}
{{ f.input('agreement_company_shortname',['label':'合同公司简称', 'width': '50%']) }}

{{ f.file('avatar',['label':'上传Icon']) }}
{{ f.input('sms_sign',['label':'短信签名','width': '50%']) }}
{{ f.input('icp',['label':'ICP备案','width': '50%']) }}

{{ f.input('ios_client_theme_test_version',[ 'label': 'ios客户端主题-测试版本号(整数, 0:不指定)', 'width':'50%']) }}
{{ f.input('ios_client_theme_foreign_version_code',[ 'label': 'ios客户端主题-海外版本号(整数, 0:不指定)', 'width':'50%']) }}
{{ f.input('apple_stable_version',[ 'label': '苹果线上稳定版本号(整数,未上线填0)','width': '50%' ]) }}
{{ f.input('android_stable_version',[ 'label': '安卓稳定版本号(整数,未上线填0)','width': '50%' ]) }}
<div class="error_reason" style="color: red;"></div>

{{ f.submit('保存') }}
{{ f.end }}
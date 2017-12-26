{% set f = simple_form(c('/admin/product_channels/update_weixin_config/',product_channel.id), product_channel,[ 'method': 'post' , 'class': 'ajax_model_form']) %}

{{ f.input('weixin_name',['label':'名称']) }}
{{ f.input('weixin_appid',['label':'appid']) }}
{{ f.input('weixin_secret',['label':'密钥']) }}
{{ f.input('weixin_token',['label':'token', 'width':'50%']) }}
{{ f.input('weixin_no',['label':'微信号', 'width':'50%']) }}
{{ f.input('weixin_domain',['label':'域名']) }}
{{ f.select('weixin_theme', ['label': '网页模板', 'collection': weixin_themes ]) }}
{{ f.input('weixin_welcome',['label':'欢迎语']) }}

{{ f.file('weixin_qrcode',['label':'上传二维码图片']) }}
{{ f.input('weixin_fr',['label':'来源渠道']) }}
{{ f.input('weixin_white_list',['label':'微信白名单']) }}
{{ f.submit('保存') }}
<div class="error_reason" style="color:red"></div>
{{ f.end }}
{% set f = simple_form([ 'admin', account_bank ], ['enctype': 'multipart/form-data', 'class':'ajax_model_form']) %}

{{ f.select('code',['label':'名称', 'collection': AccountBanks.BANK_CODE,'width':'33%']) }}
{{ f.select('status',[ 'label':'状态' , 'collection': AccountBanks.STATUS, 'width':'33%']) }}
{{ f.input('rank',['label':'排序', 'width':'33%']) }}
{{ f.file('icon', ['label': 'ICON','image': account_bank.icon_small_url]) }}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}
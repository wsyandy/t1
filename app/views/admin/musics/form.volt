{% set f = simple_form(['admin',music],['class':'ajax_model_form']) %}

{{ f.input('name',['label': '名称','width': '50%']) }}
{{ f.input('singer_name',['label': '歌手名称','width': '50%']) }}
{{ f.file('file',['label':'上传音乐文件']) }}
{{ f.input('user_id',['label': '上传用户id','width': '50%']) }}
{{ f.input('rank', ['label': '排名（不能重复)','width': '50%']) }}
{{ f.select('hot',['label':'是否热门', 'collection': Musics.HOT,'width': '33%' ]) }}
{{ f.select('status', ['label': '状态', 'collection':Musics.STATUS,'width': '33%']) }}
{{ f.select('type', ['label': '类型', 'collection':Musics.TYPE,'width': '33%']) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{% set f = simple_form(['admin', room], ['class': 'ajax_model_form', 'model': 'room']) %}

{{ f.select('hot',['label':'是否热门','collection':Rooms.HOT, 'width':'33%']) }}
{{ f.select('new',['label':'是否最新','collection':Rooms.NEW, 'width':'33%']) }}
{{ f.select('top',['label':'是否置顶','collection':Rooms.TOP, 'width':'33%']) }}
{{ f.select('status',['label':'状态','collection':Rooms.STATUS, 'width':'33%']) }}
{{ f.select('boom_config_id',['label':'爆礼物配置','collection':boom_configs, 'width':'33%']) }}
{{ f.input('hot_room_score_ratio',['label':'热门房间分数倍率','collection':Rooms.STATUS, 'width':'33%']) }}
{#{{ f.select('theme_type',['label':'电台主题','collection':Rooms.THEME_TYPE, 'width':'100%']) }}#}
{#{{ f.select('novice',['label':'新人房间','collection':Rooms.NOVICE, 'width':'50%']) }}#}
{#{{ f.select('green',['label':'绿色房间','collection':Rooms.GREEN, 'width':'100%']) }}#}

<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}
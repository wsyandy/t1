{% set f = simple_form([ 'admin', union_level_config ], ['enctype': 'multipart/form-data', 'class':'ajax_model_form']) %}

{{ f.input('union_level', [ 'label':'家族等级','width':'50%' ]) }}
{{ f.input('name', [ 'label':'等级名称','width':'50%' ]) }}
{{ f.input('grading_score', [ 'label':'保级积分','width':'50%' ]) }}
{{ f.input('promote_score', [ 'label':'晋级积分','width':'50%' ]) }}
{{ f.input('hot_room_seat_num', [ 'label':'奖励热门推荐位数','width':'50%' ]) }}
{{ f.select('union_user_award_type',['label':'奖励类型', 'collection': UnionLevelConfigs.UNION_USER_AWARD_TYPE, 'width':'50%']) }}
{{ f.input('award_amount', [ 'label':'奖励金额','width':'50%' ]) }}
{{ f.input('ratio_addition', [ 'label':'佣金比例','width':'50%' ]) }}
{{ f.file('icon', ['label': '图片','width':'100%']) }}
<div class="error_reason" style="color: red;"></div>
{{ f.submit('保存') }}
{{ f.end }}

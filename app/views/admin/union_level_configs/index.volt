<a href="/admin/union_level_configs/new" class="modal_action">新建</a>


{% macro icon_image(union_level_config) %}
    <img src="{{ union_level_config.icon_url }}" height="50"/>
{% endmacro %}

{{ simple_table(union_level_configs, ['ID': 'id','名称':'name','图标':'icon_image','保级积分':'grading_score','晋级积分':'promote_score','热门推荐位数':'hot_room_seat_num','奖励类型':'union_user_award_type_text',
'奖励金额':'award_amount','佣金比例':'ratio_addition','编辑':'edit_link']) }}

<script type="text/template" id="union_level_config_tpl">
    <tr id="union_level_config_${union_level_config.id}">
        <td>${union_level_config.id}</td>
        <td>${union_level_config.name}</td>
        <td><img src="${union_level_config.icon_url}" alt="" height="50"></td>
        <td>${union_level_config.grading_score}</td>
        <td>${union_level_config.promote_score}</td>
        <td>${union_level_config.hot_room_seat_num}</td>
        <td>${union_level_config.union_user_award_type_text}</td>
        <td>${union_level_config.award_amount}</td>
        <td>${union_level_config.ratio_addition}</td>
        <td><a href="/admin/union_level_configs/edit/${union_level_config.id}" class="modal_action">编辑</a></td>
    </tr>
</script>
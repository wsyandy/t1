<a href="/admin/draw_configs/new" class="modal_action">新建</a>

{{ simple_table(draw_configs,['id':'id','排序':'rank', '名称': 'name',
'类型':'type','数量':'number','概率':'rate','礼物ID':'gift_id','礼物数量':'gift_num', '每天限制数量':'day_limit_num','状态':'status_text','时间':'created_at_text','编辑': 'edit_link']) }}

<script type="text/template" id="draw_config_tpl">
    <tr id="draw_config_${draw_config.id}">
        <td>${draw_config.id}</td>
        <td>${draw_config.rank}</td>
        <td>${draw_config.name}</td>
        <td>${draw_config.type}</td>
        <td>${draw_config.number}</td>
        <td>${draw_config.rate}</td>
        <td>${draw_config.gift_id}</td>
        <td>${draw_config.gift_num}</td>
        <td>${draw_config.day_limit_num}</td>
        <td>${draw_config.status_text}</td>
        <td>${draw_config.created_at_text}</td>
        <td>
            <a href="/admin/draw_configs/edit/${draw_config.id}" class="modal_action">编辑</a><br/>
        </td>
    </tr>
</script>


<script type="text/javascript">
    $(function () {
        $("#product_channel_id_eq").change(function () {
            $("#search_form").submit();
        });
    });
</script>
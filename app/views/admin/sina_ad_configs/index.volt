<a href="/admin/sina_ad_configs/new" class="modal_action">新建</a>

{%- macro operate_lind(object) %}
    <a href="/admin/sina_ad_configs/edit?id={{ object.id }}" class="modal_action">编辑</a>
{%- endmacro %}

{{ simple_table(sina_ad_configs, ['id': 'id','名称': 'name','广告组ID': 'group_id',
    '转化ID':'convid','TOKEN':'token','平台':'platform_text',
'操作者':'operator_username', '更新时间':'updated_at_text','操作': 'operate_lind']) }}

<script type="text/template" id="sina_ad_config_tpl">
    <tr id="sina_ad_config_${sina_ad_config.id}">
        <td>${sina_ad_config.id}</td>
        <td>${sina_ad_config.name}</td>
        <td>${sina_ad_config.group_id}</td>
        <td>${sina_ad_config.convid}</td>
        <td>${sina_ad_config.token}</td>
        <td>${sina_ad_config.platform_text}</td>
        <td>${sina_ad_config.operator_username}</td>
        <td>${sina_ad_config.updated_at_text}</td>
        <td><a href="/admin/sina_ad_configs/edit/${sina_ad_config.id}" class="modal_action">编辑</a></td>
    </tr>
</script>


<script type="text/javascript">
    $(function () {

        $(document).on('click', '.user_action_sets', function (event) {
            event.preventDefault();
            if (confirm('确定创建?')) {

                var self = $(this);
                var url = self.attr("href");
                $.get(url, function (resp) {
                    alert(resp.error_reason);
                    location.reload();
                });
            }
            return false;
        });

    });
</script>
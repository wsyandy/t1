<a href="/admin/boom_configs/new" class="modal_action">新增</a>


{%- macro svga_link(boom_config) %}
    <img src="{{ boom_config.svga_image_small_url }}" height="50" width="50"/>
{%- endmacro %}

{%- macro edit_link(boom_config) %}
    <a href="/admin/boom_configs/edit/{{ boom_config.id }}" class="modal_action">编辑</a>
{%- endmacro %}

{{ simple_table(boom_configs, [
"ID": 'id', "名称": 'name', '开始数值':'start_value',"总数值": 'total_value',"svga图":"svga_link", "排名": 'rank','状态':'status_text', '创建时间': 'created_at_text','编辑': 'edit_link'
]) }}

<script type="text/template" id="boom_config_tpl">
    <tr id="boom_config_${ boom_config.id }">
        <td>${boom_config.id}</td>
        <td>${boom_config.name}</td>
        <td>${boom_config.start_value}</td>
        <td>${boom_config.total_value}</td>
        <td><img src="${boom_config.svga_image_small_url}" height="50" width="50"/></td>
        <td>${boom_config.rank}</td>
        <td>${boom_config.status_text}</td>
        <td>${boom_config.created_at_text}</td>

        <td><a href="/admin/boom_configs/edit/${boom_config.id}" class="modal_action">编辑</a></td>
    </tr>
</script>

<script type="text/javascript">
    $(function () {
        $('.selectpicker').selectpicker();

        {% for boom_config in boom_configs %}
        {% if boom_config.status != 1 %}
        $("#boom_config_{{ boom_config.id }}").css({"background-color": "grey"});
        {% endif %}
        {% endfor %}
    })
</script>
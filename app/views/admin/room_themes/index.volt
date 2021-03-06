<a href="/admin/room_themes/new" class="modal_action">新增</a>

<form action="/admin/room_themes" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="product_channel_id_eq">产品渠道</label>
    <select name="product_channel_id" id="product_channel_id_eq" class="selectpicker" data-live-search="true">
        {{ options(product_channels,product_channel_id,'id','name') }}
    </select>

    <label for="id_eq">ID</label>
    <input name="room_theme[id_eq]" type="text" id="id_eq"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{%- macro theme_image_link(room_theme) %}
    <img src="{{ room_theme.theme_image_url }}" width="50"/>
{%- endmacro %}

{%- macro icon_link(room_theme) %}
    <img src="{{ room_theme.icon_url }}" width="50"/>
{%- endmacro %}

{%- macro edit_link(room_theme) %}
    <a href="/admin/room_themes/edit/{{ room_theme.id }}" class="modal_action">编辑</a>
{%- endmacro %}

{%- macro product_channel_link(object) %}
    <a href="/admin/room_themes/product_channel_ids?id={{ object.id }}" class="modal_action">渠道配置({{ object.product_channel_num }})</a>
{% endmacro %}

{%- macro platforms_link(object) %}
    <a href="/admin/room_themes/platforms?id={{ object.id }}" class="modal_action">平台配置({{ object.platform_num }})</a>
{% endmacro %}

共{{ room_themes.total_entries }}个

{{ simple_table(room_themes, [
"ID": 'id', "名称": 'name',"背景图": 'theme_image_link',"图标": 'icon_link',
"有效": 'status_text', "排序":'rank', '产品渠道':'product_channel_link','平台配置':'platforms_link','编辑': 'edit_link'
]) }}

<script type="text/template" id="room_theme_tpl">
    <tr id="room_theme_${ room_theme.id }">
        <td>${room_theme.id}</td>
        <td>${room_theme.name}</td>
        <td><img src="${room_theme.theme_image_url}" width="30"></td>
        <td><img src="${room_theme.icon_url}" width="30"></td>
        <td>${room_theme.status_text}</td>
        <td>${room_theme.rank}</td>
        <td><a href="/admin/room_themes/product_channel_ids?id=${ room_theme.id }" class="modal_action">渠道配置(${ room_theme.product_channel_num })</a></td>
        <td><a href="/admin/room_themes/platforms?id=${ room_theme.id }" class="modal_action">平台配置(${ room_theme.platform_num })</a></td>
        <td><a href="/admin/room_themes/edit/${room_theme.id}" class="modal_action">编辑</a></td>
    </tr>
</script>

<script type="text/javascript">
    $(function () {
        $('.selectpicker').selectpicker();

        {% for room_theme in room_themes %}
        {% if room_theme.status != 1 %}
        $("#room_theme_{{ room_theme.id }}").css({"background-color": "grey"});
        {% endif %}
        {% endfor %}
    })
</script>
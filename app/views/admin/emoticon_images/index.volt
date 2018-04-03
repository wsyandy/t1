<a href="/admin/emoticon_images/new" class="modal_action">新增</a>

{%- macro image_link(emoticon_image) %}
    <img src="{{ emoticon_image.image_url }}" width="50"/>
{%- endmacro %}

{%- macro dynamic_image_link(emoticon_image) %}
    <img src="{{ emoticon_image.dynamic_image_url }}" width="50"/>
{%- endmacro %}

{%- macro edit_link(emoticon_image) %}
    <a href="/admin/emoticon_images/edit/{{ emoticon_image.id }}" class="modal_action">编辑</a>
{%- endmacro %}

共{{ emoticon_images.total_entries }}个

{{ simple_table(emoticon_images, [
"ID": 'id', "名称": 'name', "code": 'code', '持续时间': 'duration',
"图片": 'image_link',"动态图": 'dynamic_image_link',
"有效": 'status_text', "排序": 'rank', '编辑': 'edit_link'
]) }}

<script type="text/template" id="emoticon_image_tpl">
    <tr id="emoticon_image_${ emoticon_image.id }">
        <td>${emoticon_image.id}</td>
        <td>${emoticon_image.name}</td>
        <td>${emoticon_image.code}</td>
        <td>${emoticon_image.duration}</td>
        <td><img src="${emoticon_image.image_url}" width="30"></td>
        <td><img src="${emoticon_image.dynamic_image_url}" width="30"></td>
        <td>${emoticon_image.status_text}</td>
        <td>${emoticon_image.rank}</td>
        <td><a href="/admin/emoticon_images/edit/${emoticon_image.id}" class="modal_action">编辑</a></td>
    </tr>
</script>

<script type="text/javascript">
    $(function () {
        $('.selectpicker').selectpicker();

        {% for emoticon_image in emoticon_images %}
        {% if emoticon_image.status != 1 %}
        $("#emoticon_image_{{ emoticon_image.id }}").css({"background-color": "grey"});
        {% endif %}
        {% endfor %}
    });
</script>
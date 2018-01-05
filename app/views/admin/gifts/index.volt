<a href="/admin/gifts/new" class="modal_action">新增</a>

{%- macro image_link(gift) %}
    <img src="{{ gift.image_small_url }}" width="30" />
{%- endmacro %}

{%- macro dynamic_image_link(gift) %}
    <img src="{{ gift.dynamic_image_url }}" width="30" />
{%- endmacro %}

{%- macro edit_link(gift)  %}
    <a href="/admin/gifts/edit/{{ gift.id }}" class="modal_action">编辑</a>
{%- endmacro %}

共{{ gifts.total_entries }}个

{{ simple_table(gifts, [
    "ID": 'id', "名称": 'name', "价格": 'amount', "图片": 'image_link',
    "动态图": 'dynamic_image_link',
    "有效": 'status_text', "排序": 'rank', '编辑': 'edit_link'
]) }}

<script type="text/template" id="gift_tpl">
    <tr id="gift_${ gift.id }">
        <td>${gift.id}</td>
        <td>${gift.name}</td>
        <td>${gift.amount}</td>
        <td><img src="${gift.image_small_url}" width="30"></td>
        <td><img src="${gift.dynamic_image_url}" width="30"></td>
        <td>${gift.status_text}</td>
        <td>${gift.rank}</td>
        <td><a href="/admin/gifts/edit/${gift.id}">编辑</a></td>
    </tr>
</script>
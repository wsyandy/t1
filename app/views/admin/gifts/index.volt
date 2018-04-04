<form action="/admin/gifts" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="id_eq">ID</label>
    <input name="gift[id_eq]" type="text" id="id_eq"/>

    <label for="gift_eq">礼物类型</label>
    <select name="gift[type_eq]" id="type_eq">
        {{ options(Gifts.TYPE, type) }}
    </select>

    <label for="pay_type_eq">支付类型</label>
    <select name="gift[pay_type_eq]" id="pay_type_eq">
        {{ options(Gifts.PAY_TYPE, pay_type) }}
    </select>


    <button type="submit" class="ui button">搜索</button>
</form>

<a href="/admin/gifts/new" class="modal_action">新增</a>

{%- macro image_link(gift) %}
    <img src="{{ gift.image_small_url }}" width="30"/>
{%- endmacro %}

{%- macro big_image_link(gift) %}
    <img src="{{ gift.image_big_url }}" width="30"/>
{%- endmacro %}

{%- macro dynamic_image_link(gift) %}
    <img src="{{ gift.dynamic_image_url }}" width="30"/>
{%- endmacro %}

{%- macro edit_link(gift) %}
    <a href="/admin/gifts/edit/{{ gift.id }}" class="modal_action">编辑</a>
{%- endmacro %}

共{{ gifts.total_entries }}个

{% if isAllowed('gift_resources', 'index') %}
    <a href="/admin/gift_resources">svga礼物资源</a>
{% endif %}

{%- macro product_channel_link(object) %}
    <a href="/admin/gifts/product_channel_ids?id={{ object.id }}" class="modal_action">渠道配置</a>
{% endmacro %}

{%- macro platforms_link(object) %}
    <a href="/admin/gifts/platforms?id={{ object.id }}" class="modal_action">平台配置</a>
{% endmacro %}

{{ simple_table(gifts, [
"ID": 'id', "名称": 'name', "价格": 'amount', "图片": 'image_link', '大图': 'big_image_link',"动态图": 'dynamic_image_link',
'渲染类型':'render_type_text',"有效": 'status_text','礼物类型':'type_text','支付类型':'pay_type_text',"排序": 'rank',
'产品渠道':'product_channel_link','平台配置':'platforms_link','编辑': 'edit_link'
]) }}

<script type="text/template" id="gift_tpl">
    <tr id="gift_${ gift.id }">
        <td>${gift.id}</td>
        <td>${gift.name}</td>
        <td>${gift.amount}</td>
        <td><img src="${gift.image_small_url}" width="30"></td>
        <td><img src="${gift.image_big_url}" width="30"></td>
        <td><img src="${gift.dynamic_image_url}" width="30"></td>
        <td>${gift.render_type_text}</td>
        <td>${gift.status_text}</td>
        <td>${gift.type_text}</td>
        <td>${gift.pay_type_text}</td>
        <td>${gift.rank}</td>
        <td><a href="/admin/gifts/edit/${gift.id}" class="modal_action">编辑</a></td>
    </tr>
</script>


<script type="text/javascript">
    $(function () {
        $('.selectpicker').selectpicker();

        {% for gift in gifts %}
        {% if gift.status != 1 %}
        $("#gift_{{ gift.id }}").css({"background-color": "grey"});
        {% endif %}
        {% endfor %}
    })
</script>
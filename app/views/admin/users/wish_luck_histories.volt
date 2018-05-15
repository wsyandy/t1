<form action="/admin/users/wish_luck_histories" method="get" class="search_form" autocomplete="off">

    <label for="name_eq">产品渠道名称</label>
    <select name="user[product_channel_id_eq]" id="product_channel_id_eq" class="selectpicker" data-live-search="true">
        {{ options(all_product_channels, '', 'id', 'name') }}
    </select>

    <button type="submit" class="ui button">搜索</button>
</form>

{% macro avatar_image(user) %}
    <img src="{{ user.avatar_small_url }}" height="50"/>
{% endmacro %}

{{ simple_table(wish_luck_users, ['ID': 'id','UID':'uid', '头像': 'avatar_image','获奖人姓名': 'nickname','手机号': 'mobile','获奖时间':'winner_at_text'
 ]) }}

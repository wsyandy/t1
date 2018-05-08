<form action="/admin/users/blocked_nearby_user_list" method="post" enctype="multipart/form-data">
    <a href="/admin/users/add_blocked_nearby_user" class="modal_action">新建</a>

    <label for="user_uid">用户uid</label>
    <input type="text" name="user_uid" id="user_uid"/>
    <input type="submit" name="submit" value="搜索"/>
</form>

{% macro delete_blocked_link(user) %}
    <a class="delete_action" href="/admin/users/delete_blocked_nearby_user?user_uid={{ user.uid }}">删除</a>
{% endmacro %}

{{ simple_table(users, ['uid': 'uid', '昵称': 'nickname','删除': 'delete_blocked_link']) }}
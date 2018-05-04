<form action="/admin/users/reserved" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="id">ID</label>
    <input name="id" type="text" id="id"/>

    <button type="submit" class="ui button">搜索</button>
</form>

<a href="/admin/users/select_good_no_list">靓号筛选列表</a>

{%- macro select_good_num(user) %}
    <a href="#" class="add_good_num" data-good_num="{{ user.uid }}">添加至选择靓号列表</a>
{%- endmacro %}

{{ simple_table(users, ['UID': 'uid', '添加至选择靓号列表':'select_good_num']) }}

<script>

    $(function () {

        $(".add_good_num").click(function (e) {
            e.preventDefault();

            var good_num = $(this).data('good_num');

            $.post('/admin/users/add_select_good_num', {good_num: good_num}, function (resp) {

                if (0 != resp.error_code) {
                    alert(resp.error_reason);
                    return;
                }

            })
        })
    })

</script>
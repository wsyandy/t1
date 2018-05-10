<a href="/admin/rooms/new_hot_search_keywrods" class="modal_action">新建</a>

{%- macro operator_link(room) %}
    <a href="/admin/rooms/new_hot_search_keywrods?room[keyword]={{ room.keyword }}" class="modal_action">编辑</a>
    <a href="/admin/rooms/delete_search_keywrods?room[keyword]={{ room.keyword }}" id="delete_search_keywrods">删除</a><br/>
{%- endmacro %}

{{ simple_table(rooms, ['名称': 'keyword', '排序':'rank','操作': 'operator_link']) }}

<script>
    $('body').on('click', '#delete_search_keywrods', function (e) {
        e.preventDefault();
        if (confirm('确认删除？')) {
            var href = $(this).attr('href');
            $.post(href, '', function (resp) {
                location.reload();
            });
        }
    });
</script>
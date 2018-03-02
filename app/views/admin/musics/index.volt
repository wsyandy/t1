<a href="/admin/musics/new" class="modal_action">新增</a>


{%- macro edit_link(music) %}
    <a href="/admin/musics/edit/{{ music.id }}" class="modal_action">编辑</a>
{%- endmacro %}


{% macro user_info(music) %}
    {% if isAllowed('users','index') %}
        姓名:<a href="/admin/users?user[id_eq]={{ music.user_id }}">{{ music.user_nickname }}</a><br/>
    {% endif %}
    性别:{{ music.user.sex_text }}<br/>
    手机号码:{{ music.user_mobile }}<br/>
{% endmacro %}

{{ simple_table(musics, [
    "ID": 'id',"上传时间":"created_at_text", "上传者信息":"user_info","名称": 'name',"歌手名称": 'singer_name',"文件大小": 'file_size_text', '类型':'type_text',
    "有效": 'status_text', "排名": 'rank','编辑': 'edit_link'
]) }}

<script type="text/template" id="music_tpl">
    <tr id="music_${ music.id }">
        <td>${music.id}</td>
        <td>${music.created_at_text}</td>
        <td>
            姓名:<a href="/admin/users?user[id_eq]=${music.user_id}">${music.user_nickname}</a><br/>
            性别:${music.sex_text}<br/>
            手机号码:${music.user_mobile}
        </td>
        <td>${music.name}</td>
        <td>${music.singer_name}</td>
        <td>${music.file_size_text}</td>
        <td>${music.type_text}</td>
        <td>${music.status_text}</td>
        <td>${music.rank}</td>
        <td><a href="/admin/musics/edit/${music.id}" class="modal_action">编辑</a></td>
    </tr>
</script>
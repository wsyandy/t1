<a href="/admin/games/new" class="modal_action">新增</a>

<form action="/admin/games" method="get" class="search_form" autocomplete="off" id="search_form">
    <label for="id_eq">ID</label>
    <input name="game[id_eq]" type="text" id="id_eq"/>

    <label for="name_eq">游戏名称</label>
    <input name="game[name_eq]" type="text" id="name_eq"/>

    <button type="submit" class="ui button">搜索</button>
</form>

{%- macro edit_link(game) %}
    <a href="/admin/games/edit/{{ game.id }}" class="modal_action">编辑</a>
{%- endmacro %}

{%- macro icon_link(game) %}
    <img src="{{ game.icon_small_url }}" height="50" width="50"/>
{%- endmacro %}


{{ simple_table(games, [
"ID": 'id',"游戏名称":"name",'游戏图标':"icon_link","跳转路径":"url","状态": 'status_text','编辑': 'edit_link'
]) }}

<script type="text/template" id="game_tpl">
    <tr id="game_${ game.id }">
        <td>${game.id}</td>
        <td>${game.name}</td>
        <td><img src="${game.icon_small_url}" height="50" width="50"/></td>
        <td>${game.url}</td>
        <td>${game.status_text}</td>
        <td><a href="/admin/games/edit/${game.id}" class="modal_action">编辑</a></td>
    </tr>
</script>
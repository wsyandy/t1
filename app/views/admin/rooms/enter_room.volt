<form action="/admin/rooms/enter_room?room_id={{ room.id }}" method="post" >
    <div class="form-group">
        <label for="user_id">虚拟用户ID</label>
        <select type="text" name="user_id" id="user_id" class="form-control">
            {% for user in users %}
                <option value="{{ user.id }}"> {{ user.nickname }}</option>
            {% endfor %}
        </select>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-default btn-primary" name="submit" value="提交"/>
    </div>
</form>

<form action="/admin/users/enter_room?user_id={{ user.id }}" method="post" >
    <div class="form-group">
        <label for="room_id">房间ID</label>
        <select type="text" name="room_id" id="room_id" class="form-control">
            {% for room in rooms %}
                <option value="{{ room.id }}"> {{ room.name }}</option>
            {% endfor %}
        </select>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-default btn-primary" name="submit" value="提交"/>
    </div>
</form>

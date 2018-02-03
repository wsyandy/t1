<form action="/admin/rooms/enter_room?user_id={{ user.id }}" method="post" >
    <div class="form-group">
        <label for="sender_id">发送者</label>
        <select type="text" name="sender_id" id="sender_id" class="form-control">
            {% for sender in senders %}
                <option value="{{ sender.id }}"> {{ sender.name }}</option>
            {% endfor %}
        </select>
        <label for="gift_id">礼物ID</label>
        <select type="text" name="gift_id" id="gift_id" class="form-control">
            {% for gift in gifts %}
                <option value="{{ gift.id }}"> {{ gift.name }}</option>
            {% endfor %}
        </select>
        <div class="form-group">
            <label for="num">数量</label>
            <input type="number" name="num" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-default btn-primary" name="submit" value="提交"/>
    </div>
</form>
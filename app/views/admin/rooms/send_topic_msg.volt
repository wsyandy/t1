<form action="/admin/rooms/enter_room?user_id={{ user.id }}" method="post" >
    <div class="form-group">
        <div class="form-group">
            <label>消息内容</label>
            <input type="text" name="content" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-default btn-primary" name="submit" value="提交"/>
    </div>
</form>
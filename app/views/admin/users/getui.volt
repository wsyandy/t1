<form action="/admin/users/getui?receiver_id={{ receiver.id }}" method="post">
    <label class="control-label">push_token: </label>{{ receiver.push_token }}
    <div class="form-group">
        <label class="control-label">标题</label>
            <input type="text" name="title" class="form-control">
    </div>

    <div class="form-group">
        <label class="control-label">内容</label>
            <input type="text" name="body" class="form-control">
    </div>
    <div class="form-group">
        <label class="control-label">client_url</label>
        <input type="text" name="client_url" class="form-control">
    </div>

    <div class="form-group">
        <label>消息类型</label>
        <select name="show_type" class="form-control">
            <option value="0">普通</option>
            <option value="1">下沉消息</option>
        </select>
    </div>


    <div class="form-group">
            <input type="submit" value="发送" class="btn btn-default btn-primary">
    </div>
</form>
<form action='/admin/users/send_message?id={{ user.id }}' method="post">
    <div class="form-group">
        <label>消息内容</label>
        <textarea name="content" class="form-control"></textarea>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-default btn-primary" value="发送">
    </div>
</form>
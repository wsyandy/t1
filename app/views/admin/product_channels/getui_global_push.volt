<form action="/admin/product_channels/getui_global_push?id={{ product_channel.id }}" method="post">
    <div class="form-group">
        <label class="control-label">标题</label>
        <div class="controls">
            <input type="text" name="title" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label">内容</label>
        <div class="controls">
            <input type="text" name="body" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label">平台</label>
        <div class="controls">
            <select name="platform" class="form-control">
                <option>android</option>
                <option>ios</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <input type="submit" value="发送" class="btn btn-default btn-primary">
    </div>
</form>
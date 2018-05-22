<form action='/admin/users/send_message?id={{ user.id }}' method="post">


    {% if isDevelopment() %}
        <div class="form-group">
            <label>消息类型</label>
            <select name="content_type" class="form-control">
                <option value="text/news" selected="selected">图文</option>
                <option value="text/plain" selected="selected">文本</option>
            </select>
        </div>

        <div class="form-group">
            <label>标题</label>
            <input type="text" name="title" class="form-control"/>
        </div>


        <div class="form-group">
            <label>跳转地址</label>
            <input type="text" name="url" class="form-control"/>
        </div>

        <div class="form-group">
            <label>图片地址</label>
            <input type="text" name="image_url" class="form-control"/>
        </div>

    {% endif %}

    <div class="form-group">
        <label>消息内容</label>
        <textarea name="content" class="form-control"></textarea>
    </div>

    <div class="form-group">
        <input type="submit" class="btn btn-default btn-primary" value="发送">
    </div>
</form>
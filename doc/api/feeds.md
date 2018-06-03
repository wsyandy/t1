# 动态接口

### 1 动态列表接口

> http-get ```/api/feeds```

##### 1.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| type |动态类型|string|是|new:最新:follow关注:essence精华|
| feed_topic_id |动态话题id|string|是||
| page |当前页码|int|否||
| per_page |每页的条数|int|是|每页的数据条数，默认10|

##### 1.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    current_page  当前页数
    total_page   总页数
    total_entries   总记录数
    feeds:[
        {
           id string 动态id
           sex int 性别  0:女 1:男
           avatar_url string 正常图像
           avatar_small_url string 小尺寸图像
           nickname string 昵称
           created_at_text string 拉黑时间
           created_at int 创建时间戳
           share_users_num int 分享人数
           is_liked boolean 是否点过赞
           is_disliked boolean 是否彩果
           is_follow boolean 是否关注
           duration int 语音时长
           voice_file_url string 语音地址
           content string 内容
           like_users_num int 点赞人数
           comment_users_num int 评论人数
           user_id int 动态用户id
           feed_images:[
                {
                    image_big_url : 大图
                    image_small_url : 小图
                }
           ] 图片地址
           location string 地址
        } 
    ]
}
```

### 2 创建动态

> http-post ```/api/feeds```

##### 2.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| content |动态内容|string|否|||
| location |地址|string|否|||
| feed_topic_id |动态话题id|string|是|||
| duration |动态语音时长|int|是|||
| voice_file |动态语音文件|file|是|||
| feed_image 1~9 |动态语音图片对应的9张图片|file|是|||

##### 2.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
}
```

### 3 动态点赞

> http-post ```/api/feeds/like```

##### 3.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| id |动态id|string|否|||

##### 3.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
}
```

### 4 踩动态

> http-post ```/api/feeds/dislike```

##### 4.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| id |动态id|string|否|||

##### 4.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
}
```

### 5 关注动态

> http-post ```/api/feeds/follow```

##### 5.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| id |动态id|string|否|||

##### 5.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
}
```

### 6 动态详情

> http-get ```/api/feeds/detail```

##### 6.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| id |动态id|string|否|||

##### 6.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
    id string 动态id
    sex int 性别  0:女 1:男
    avatar_url string 正常图像
    avatar_small_url string 小尺寸图像
    nickname string 昵称
    created_at_text string 拉黑时间
    created_at int 创建时间戳
    share_users_num int 分享人数
    is_liked boolean 是否点过赞
    is_disliked boolean 是否彩果
    is_follow boolean 是否关注
    duration int 语音时长
    voice_file_url string 语音地址
    content string 内容
    like_users_num int 点赞人数
    comment_users_num int 评论人数
    user_id int 动态用户id
    feed_images:[
        {
            image_big_url : 大图
            image_small_url : 小图
        }
    ] 图片地址
    location string 地址
}
```
### 7 动态评论

> http-post ```/api/feed_comments```

##### 7.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| feed_id |动态id|string|否|||
| content |评论内容|string|否|||

##### 7.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
}
```

### 8 动态评论列表

> http-get ```/api/feed_comments```

##### 8.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| feed_id |动态id|string|否|||

##### 8.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
	feed_comments:{
	    id string 评论id
	    content string 评论的内容
	    created_at int 评论的时间戳
	    user_id int 用户id
	    nickname string 用户昵称
	    avatar_url string 用户头像地址
	}
}
```


### 9 创建动态话题

> http-post ```/api/feed_topics```

##### 9.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| name |动态话题名称|string|否|||

##### 9.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
    id string 话题id 
    name string 话题名称
}
```


### 10 动态话题列表

> http-get ```/api/feed_topics```

##### 10.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| name |话题名称|string|是|||

##### 10.2 回应参数说明
```
{
	error_code  0 成功，非0失败
	error_reason  失败原因，默认为空
	feed_topics:{
        id string 话题id
        name string 话题名称
        feed_num int 动态数量
        browse_users_num int 浏览人数
        avatar_small_url string 用户头像
	}
}
```
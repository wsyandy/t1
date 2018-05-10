# 系统通知

### 1 环信透传

##### 1.1 透传参数说明
```
{
    "target_type":"users",     // users 给用户发消息。chatgroups 给群发消息，chatrooms 给聊天室发消息
    "target":["testb","testc"], // 注意这里需要用数组，数组长度建议不大于20，即使只有  
                                // 一个用户u1或者群组，也要用数组形式 ['u1']，给用户发  
                                // 送时数组元素是用户名，给群组发送时数组元素是groupid
    "msg":{  //消息内容
        "type":"cmd",  // 消息类型(透传）
        "action":"action1"
    },
    "from":"1"  //表示消息发送者。
    "ext" {
       id: 1_2_xxx
       sender_id: 1
       receiver_id: 2
       created_at: 1111
       content: ''
       content_type: 'text/plain'
    } // 扩展属性
}
```

### 2 系统消息拉取

> http-get ```/api/chats```

##### 2.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|user_id|用户id|int|否|值为1表示系统消息|
|page|页码|int|是|空值表示1|
|per_page|每页个数|是|空值默认是30|||

##### 2.2 返回参数说明
```
{
    error_code: 0/-1
    error_reason: ''
    total_entries: 30
    current_page: 1
    total_pages: 10
    id: 0
    nickname: ''
    avatar_url: ''
    chats: [
        {
            id: ''
            content_type: ''
            content: ''
            created_at: ''
        }
        ...
    ]
}
```

### 3 未读消息个数 

> http-get ```/api/chats/unread_num```

##### 3.1 请求参数说明
公共参数

##### 3.2 返回参数说明
```
{
    error_code: 0/-1
    error_reason: ''
    unread_num 未读消息个数
}
```

### 4 发送消息 

> http-post ```/api/chats```

##### 4.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|user_id|接收用户id|int|否||
|content|消息内容|string|是||
|content_type|消息类型|string|否|文本类型:text图片类型:image语音类型:voice|
|file|消息文件|file|是|||

##### 4.2 返回参数说明
```
{
    error_code: 0/-1
    error_reason: ''
   
}
```
### 5 消息类型备注

```
文本类型:text/plain, 文本json: text/json, 文本html: text/html,文本emoji:text/emoji, 
普通图片类型:image/image, gif图片类型:image/gif, 礼物: goods/gift, 音频mp3: audio/mp3, 
视频mp4: video/mp4 图文消息: text/news
```
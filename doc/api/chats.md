# 系统通知

### 1 环信透传

### 2 系统消息拉取

> http-get ```/api/chats```

##### 2.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|user_id|用户id|int|否|值为0表示系统消息|

##### 2.2 返回参数说明
```
{
    error_code: 0/-1
    error_reason: ''
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
    ]
}
```
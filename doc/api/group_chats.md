#群聊

#1、创建群聊

> http-post ```/api/group_chats```
##### 1.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|name|群聊名称|string|否||
|introduce|群聊简介|string|否||
|join_type|加群方式|string|否|all直接加入 review需要审核|
|avatar_file|群头像|file|否|||

##### 1.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    group_chat: {
            id: int 群聊id,
            user_id: 群主id,
            name: 群聊名称,
            introduce: 群聊简介,
            avatar: 群头像,
            uid: 群号,
            status: int 群状态 1正常 0解散,
            join_type: 加群方式 all直接进入 review需要审核,
            created_at  创建时间戳
            last_at  最后活跃时间
            chat true 全员可聊天  false 全员禁言
        },     
}
```
#2、修改群聊信息

> http-post ```/api/group_chats/update```
##### 2.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|群聊id|int|否||
|name|群聊名称|string|是||
|introduce|群聊简介|string|是||
|avatar_file|群头像|file|是|||

##### 2.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    group_chat: {
            id: int 群聊id,
            user_id: 群主id,
            name: 群聊名称,
            introduce: 群聊简介,
            avatar: 群头像,
            uid: 群号,
            status: int 群状态 1正常 0解散,
            join_type: 加群方式 all直接进入 review需要审核,
            created_at  创建时间戳
            last_at  最后活跃时间
            chat true 全员可聊天  false 全员禁言
        },     
}
```

#3、搜索群聊

> http-get ```/api/group_chats/search```
##### 3.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|keyword|群号或关键词||否|||

##### 3.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    group_chats: [
        {
            id: int 群聊id,
            user_id: 群主id,
            name: 群聊名称,
            introduce: 群聊简介,
            avatar: 群头像,
            uid: 群号,
            status: int 群状态 1正常 0解散,
            join_type: 加群方式 all直接进入 review需要审核,
            created_at  创建时间戳
            last_at  最后活跃时间
            chat true 全员可聊天  false 全员禁言
        },
        {
            id: int 群聊id,
            user_id: 群主id,
            name: 群聊名称,
            introduce: 群聊简介,
            avatar: 群头像,
            uid: 群号,
            status: int 群状态 1正常 0解散,
            join_type: 加群方式 all直接进入 review需要审核,
            created_at  创建时间戳
            last_at  最后活跃时间
            chat true 全员可聊天  false 全员禁言
        },
    ]     
}
```
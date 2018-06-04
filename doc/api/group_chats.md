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

#4、加入群

> http-post ```/api/group_chats/add_group_chat```
##### 4.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|群聊id|int|否|||

##### 4.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    user: {
            id: int 用户ID,
            sex: 用户性别,
            avatar_small_url: 用户头像,
            nickname: 用户昵称,
            user_chat: true 用户可以聊天  false 用户被禁言,
        },
}
```
#5、退出群

> http-post ```/api/group_chats/quit_group_chat```
##### 5.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|群聊id|int|否|||

##### 5.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
}
```

#6、群主邀请入群

> http-post ```/api/group_chats/host_invite```
##### 6.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|群聊id|int|否||
|user_id|邀请用户id|int|否|||

##### 6.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
    user: {
            id: int 用户ID,
            sex: 用户性别,
            avatar_small_url: 用户头像,
            nickname: 用户昵称,
            user_chat: true 用户可以聊天  false 用户被禁言,
        },
    
}
```

#7、添加管理员

> http-post ```/api/group_chats/add_manager```
##### 7.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|群聊id|int|否||
|user_id|管理员用户id|int|否|||

##### 7.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
}
```

#8、删除管理员

> http-post ```/api/group_chats/delete_manager```
##### 8.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|群聊id|int|否||
|user_id|管理员用户id|int|否|||

##### 8.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
}
```

#9、踢出群聊

> http-post ```/api/group_chats/kick```
##### 8.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|群聊id|int|否||
|user_id|用户id|int|否|||

##### 8.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
}
```

#10、解散群

> http-post ```/api/group_chats/disband```
##### 10.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|群聊id|int|否|||


##### 10.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
}
```

#11、设置加群方式

> http-post ```/api/group_chats/set_join_type```
##### 11.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|群聊id|int|否||
|join_type|加群类型|string|否|all 直接加入  review 需要审核||

##### 11.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
}
```

#12、设置全员禁言

> http-post ```/api/group_chats/close_chat```
##### 12.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|群聊id|int|否|||

##### 12.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
}
```

#13、取消全员禁言

> http-post ```/api/group_chats/open_chat```
##### 13.1 请求参数说明
|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|id|群聊id|int|否|||

##### 13.2 回应参数说明
```
{
    error_code  0 成功，非0失败
    error_reason  失败原因，默认为空
}
```

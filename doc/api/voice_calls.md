# 语音通话

### 1 发起通话
> http-post ```/api/voice_calls```

#### 1.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
| user_id |用户ID|integer|否|接听方用户ID|

#### 1.2 返回参数说明
```
{
     error_code: 0/-1
     error_reason: ''
     error_url: ''
     call_no: ''  
     channel_name: ''
     channel_key: ''                  
}
```

### 2 通话状态上报
> http-post ```/api/voice_calls/update```

#### 2.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|call_no|发起通话返回call_no值|string|否||
|call_status|通话状态|string|否|

#### 2.2 返回参数说明
```
{
    error_code: 0/-1
    error_reason: ''
    error_url: ''
}
```

### 3 通话记录拉取
> http-get ```/api/voice_calls```

#### 3.1 请求参数说明
|参数|名称|值类型|是否可空|备注
|---|---|---|---|---|
|page|页码|integer|否||
|per_page|每页个数|integer|是|

#### 3.2 返回参数说明
```
{
    error_code: 0/-1
    error_reason: ''
    error_url: ''
    voice_calls: [
        {
            user_id: 1 
            nickname: ''
            avatar_url: ''
            call_no: ''
            created_at: 111
            duration: 10
            call_status: 'wait/no_answer/busy/refuse/cancel/hang_up'
            call_status_text: '等待接听/无应答/对方正忙/拒绝/取消/挂断'
        }
    ]
}
```
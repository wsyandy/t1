# 分享

## 1 分享详情

> http-post ```/api/shares/detail```

##### 1.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|room_id|房间ID|int|是|要分享的房间id|
|share_source|分享来源|string|否|若是从房间分享，就传room|

##### 1.2 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'    
    title string 标题
    description string 描述
    image_url string
    image_small_url  string
    url string  用户点击的链接
    share_history_id int 分享记录id。分享后，调用分享结果接口需要此参数
}
```

## 2 分享结果 

> http-post ```/api/shares/result```

##### 2.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|share_history_id|分享记录id|int|否||
|type|分享类型|int|否|1微信好友，2朋友圈，3新浪微博，4QQ，5QQ空间，6链接，7邀请卡
|status|分享状态|int|否|1失败，2成功|

##### 1.2 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'    
}
```

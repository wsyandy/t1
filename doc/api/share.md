# 分享

## 1 分享详情

> http-post ```/api/shares/detail```

##### 1.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|room_id|房间ID|int|是|要分享的房间id|
|share_source|分享来源|string|否|房间room，赚金币gold_works|

##### 1.2 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'    
    title string 标题
    description string 描述
    image_url 分享用户的头像 string
    image_small_url  产品头像 string
    url string  用户点击的链接
    share_history_id int 分享记录id。分享后，调用分享结果接口需要此参数
}
```

## 2 分享成功上报 

> http-post ```/api/shares/result```

##### 2.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|share_history_id|分享记录id|int|否||
|type|分享类型|int|否|1微信好友，2朋友圈，4QQ好友，5QQ空间，3新浪微博，6链接，7邀请卡
|status|分享状态|int|否|1成功 2失败 3取消|

##### 2.2 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'    
    gold: 用户当前的金币数量
}
```

## 3 赚金币

> http-get ```/api/shares/gold_works```

##### 3.1 请求参数说明

```
公共参数
```

##### 3.2 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'    
    gold: 剩余金币
    bind_mobile_status int 1 已绑定 2 未绑定绑定手机号码状态
    bind_mobile_gold 绑定手机号码奖励金币数量 
    sign_in_status: 签到状态
    sign_in_message: 连续签到xx天，今天/明天签到可获得xxxx金币
    gold_works:[
            {
             name  名称
             type   类型
             work_status 状态 1完成，2未完成
             work_gold: 分享能获得的金币
            }
        ...
    ]
}
```
# 信令结构

客户端和客户端通信

通知类型分为：
1. 点对点通信，客户端A通知客户端B；例如 用户A请求添加用户B为好友，并信令通知B；
2. 广播通信，通知房间内所有用户；例如 用户A送礼物给用户B，并通知房间内所有用户；


## 1.公共参数说明

### 1.1. 请求参数

参数名称|类型|是否必传|说明
---|---|---|---
model|string|Y|模块名称
action|string|Y|动作
notify_type|string|Y|通知类型，点对点ptp, 广播通信bc
timestamp|int|Y|通知发送时间
receiver_id|int|Y|接收者用户id, 广播通信时为0
data|json|Y|请求的业务数据，具体参数说明见详细接口

### 1.2. 响应参数

参数名称|类型|是否必传|备注
---|---|---|---|---
error_code|int|Y|0表示成功，非0为异常情况
error_reason|string|Y|返回失败原因,默认为空串


## 2. 添加好友

### 2.1 请求数据示例

```
{
    "model":"friends",
    "action":"add",
    "notify_type":"ptp",
    "timestamp": 1513510273,
    receiver_id: 100,
    "data":{  
       user_id int 发起请求用户ID
       sex int 性别  0:女 1:男
       avatar_small_url string 小尺寸图像
       nickname string 昵称
       introduce string 自我介绍
       created_at_text int 创建时间
    }
}

```

### 2.2 回应数据示例

```
{
    
    "error_code":0,
    "error_reason":"请求成功"
}

```
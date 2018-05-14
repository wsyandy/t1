# 背包

## 1 礼物背包

> http-get ```/api/backpacks```


##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|type|背包target的类型|integer|可空|礼物默认为1

##### 返回参数说明
```
{
    "error_code": 0,
    "error_reason": "",
    "error_url": "",
    "now_at": 1525782941,
    "current_page": 1,
    "total_page": 1,
    "total_entries": 2,
    "backpacks": [
        {
            "id": 2,
            "number": 1,
            "image_url": "http:\/\/mt-development.img-cn-hangzhou.aliyuncs.com\/chance\/gifts\/image\/5acf07d875095.png",
            "name": "飞猪",
            "svga_image_name": "",
            "render_type": "gif",
            "svga_image_url": "http:\/\/mt-development.img-cn-hangzhou.aliyuncs.com\/chance\/gifts\/image\/5acf07d875095.png@!small",
            "expire_day": 0,
            "show_rank": 0
        },
        {
            "id": 1,
            "number": 1,
            "image_url": "http:\/\/mt-development.img-cn-hangzhou.aliyuncs.com\/chance\/gifts\/image\/5acf07d875095.png",
            "name": "光电游侠",
            "svga_image_name": "",
            "render_type": "gif",
            "svga_image_url": "http:\/\/mt-development.img-cn-hangzhou.aliyuncs.com\/chance\/gifts\/image\/5acf07d875095.png@!small",
            "expire_day": 0,
            "show_rank": 0
        }
    ],
    "apple_stable_version": 19,
    "android_stable_version": 8
}
```


## 2 赠送背包礼物

> http-post /api/backpacks/

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|id|背包ID|integer|是||
|gift_num|礼物数量|integer|可空|空表示数量是1
|user_id|接收方ID|integer|是||
|src|来源|string|可|默认是room

##### 返回参数说明
````
{ 
    error_code: 0/-1/-2
    error_reason: '赠送成功/失败/余额不足'
    diamond: 用户钻石余额
    gold: 用户金币余额
    total_amount: 消费的钻石或金币数额
    pay_type: 礼物的支付类型
    model: 'gifts'
    action: 'give'
    notify_type: 'bc/ptp'
    timestamp: 1515142608
    data: {
        id: 1
        num: 10
        name: '礼物名称' 
        image_small_url: 'http://small_url'
        image_big_url: 'http://big_url',
        dynamic_image_url: 'http://dynamic_image_url',
        user_id: 1
        user_nickname: '接收方昵称'
        user_avatar_small_url 接收方头像
        sender_id: 2
        sender_nickname: '发送方昵称'
        sender_avatar_small_url 发送方头像
        amount 礼物金额
        show_rank int 礼物展示排序
        expire_time int 礼物过期时间
        music_url 礼物音效
    }
} 
````
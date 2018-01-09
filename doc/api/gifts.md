# 礼物

## 1 礼物商城

> http-get ```/api/gifts```

##### 1.1 请求参数说明

```
   公共参数
```

##### 1.2 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'  
    diamond: 100
    pay_url: 'http://order/m/xxx'
    total_page: 10
    current_page: 1
    total_entries: 100   
    gifts: [
        {
            id: 1 
            name: '礼物名称'
            image_small_url: 'https://gifts/small'
            image_big_url: 'https://gifts/big'
            dynamic_image_url: 'https://gifts/dynamic'
            amount: 100
            pay_type: 'diamond/gold'
        }
    ]
}
```

## 2 赠送礼物

> http-post ```/api/gifts```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|gift_id|礼物ID|integer|否||
|gift_num|礼物数量|integer|可空|空表示数量是1
|user_id|接收方ID|integer|否||
|src|来源|string|可|默认是room

##### 返回参数说明
````
{ 
    error_code: 0/-1/-2
    error_reason: '赠送成功/失败/余额不足'
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
        dynamic_url: 'http://dynamic_url',
        user_id: 1
        user_nickname: '接收方昵称'
        sender_id: 2
        sender_nickname: '发送方昵称'
        
    }
} 
````

## 3 收到的礼物

> http-get ```/api/user_gifts```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|page|页码|integer|否||
|page_num|每页个数|integer|可空|空表示数量20

##### 返回参数说明
````
{ 
    error_code: 0/-1
    error_reason: ''
    error_url: "",
    now_at: 1515508744,
    current_page: 1
    total_page: 100,
    total_entries: 1000
    user_gifts: [
        {
            gift_id: 1
            name: ''
            amount: 10
            pay_type 'gold/diamond'
            image_url: ''
            image_small_url: ''
            image_big_url: ''
            dynamic_image_url: ''
            num: 10
        }
        ...
    ]
}
````
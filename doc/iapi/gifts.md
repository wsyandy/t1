# 礼物

## 1 礼物商城

> http-get ```/iapi/gifts```

##### 1.1 请求参数说明

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|gift_type|礼物类型|int|否|默认为1普通礼物,2:座驾礼物|

##### 1.2 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'  
    diamond: 100
    gold: 金币
    pay_url: 'url://im/products'
    total_page: 10
    current_page: 1
    total_entries: 100   
    gifts: [
        {
            id: 1 
            name: '礼物名称'
            image_url: 'https://gifts/image'
            image_small_url: 'https://gifts/small'
            image_big_url: 'https://gifts/big'
            dynamic_image_url: 'https://gifts/dynamic'
            amount: 100
            pay_type: 'diamond/gold' 钻石 diamond gold金币
            gift_type: 1 普通礼物 2 座驾
            svga_image_name: svga 对应zip包中的图片名称
            render_type 渲染类型 gif svga
            svga_image_url svga 图片
            expire_day int 有效天数
            show_rank int 礼物展示排序
            buy_status boolean 是否购买
    ],
    products: [
        {
            id: 1
            name: ''
            amount: 10
            diamond: 100
            apple_product_no: ''
        }
        ...
    ] 如果是iOS用户,并且products不为空，客户端直接支付，否则打开pay_url支付
}
```

## 2 赠送礼物

> http-post ```/iapi/gifts```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|gift_id|礼物ID|integer|否||
|gift_num|礼物数量|integer|可空|空表示数量是1
|user_id|接收方ID 自己买座驾不需要传此参数|integer|是||
|src|来源|string|可|默认是room
|renew|续期|integer|可|座驾续期时传此参数

##### 返回参数说明
````
{ 
    error_code: 0/-1/-2
    error_reason: '赠送成功/失败/余额不足'
    diamond: 用户钻石余额
    gold: 用户金币余额
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
        sender_id: 2
        sender_nickname: '发送方昵称'
        amount 礼物金额
        show_rank int 礼物展示排序
        expire_time int 礼物过期时间
    }
} 
````

## 3 收到的礼物

> http-get ```/iapi/user_gifts```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|page|页码|integer|否||
|per_page|每页个数|integer|可空|空表示数量20
|user_id|用户ID|integer|可空|空表示查看自己收到的礼物
|car_gift|座驾礼物|integer|可空|座驾礼物需要传1
|common_gift|普通礼物|integer|可空|普通礼物需要传1

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
    total_gift_num: 礼物总个数
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
            show_rank int 礼物展示排序
        }
        ...
    ]
    user_car_gifts: [ //座驾礼物
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
            expire_day 过期天数
            svga_image_name: svga 对应zip包中的图片名称
            render_type 渲染类型 gif svga
            svga_image_url svga 图片
            status 0 未使用 1 使用中
            show_rank int 礼物展示排序
        }
        ...
    ]
}
````

## 4 礼物资源 svga使用 

> http-get ```/iapi/gift_resources```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
公共参数

##### 返回参数说明
````
{ 
    error_code: 0/-1
    error_reason: ''
    error_url: "",
    now_at: 1515508744,
    resource_file_url : 资源地址 xxx.zip
    resource_code:资源code 客户端根据此参数判断是否拉取礼物
}
````

## 5 设置我的座驾礼物

> http-post ```/iapi/gifts/set_car_gift```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|gift_id|礼物ID|integer|否|礼物id

##### 返回参数说明
````
{ 
    error_code: 0/-1
    error_reason: ''
    error_url: "",
    now_at: 1515508744
    name: ''
    image_url: ''
    image_small_url: ''
    image_big_url: ''
    dynamic_image_url: ''
    svga_image_name: svga 对应zip包中的图片名称
    render_type 渲染类型 gif svga
    svga_image_url svga 图片
    show_rank int 礼物展示排序
    status  使用状态 0未使用 1 使用中
}
````

## 6 我的礼物明细

> http-get ```/iapi/gift_orders```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|---|
|page|页码|integer|否||
|per_page|每页|integer|可空|空表示数量20|
|type|展示类型|string|可空|空默认为receive收到的礼物，send送出的礼物|

##### 返回参数说明
````
{ 
    error_code: 0/-1
    error_reason: ''
    hi_coins：总Hi币数（只有在第一页时推送）
    gift_orders：[
        {
            'name' :string 礼物名称,
            'user_name' :string 接收礼物者名称,
            'sender_name' : string 送出礼物者名称,
            'user_avatar_small_url' :'',
            'sender_avatar_small_url' :'',
            'amount' : 0,
            'gift_num' : int 礼物个数,
            'image_url' :'',
            'image_small_url' :'',
            'image_big_url':'',
            'created_at_text' :string 创建时间,
            'user_id' ： 1,
            'sender_id' ： 2,
            'pay_type'  :'diamond/gold' diamond钻石  gold金币,
            'pay_type_text' :'钻石/金币'
        }
    ]
}
````
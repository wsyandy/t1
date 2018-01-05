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
            small_image_url: 'https://gifts/small'
            big_image_url: 'https://gifts/big'
            dynamic_image_url: 'https://gifts/dynamic'
            amount: 100
            pay_type: 'diamond/gold'
        }
    ]
}
```

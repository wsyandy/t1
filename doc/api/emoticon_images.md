# 表情

## 1 表情

> http-get ```/api/emoticon_images```

##### 1.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|page|页码|int|是||
|per_page|每页|int|是|||

##### 1.2 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'  
    total_page: 
    current_page: 
    total_entries:   
    emoticon_images: [
        {
            id: int 
            name: string 表情名称
            code:  string 存放在客户端图片的标识，
                猜拳:mora  骰子:dice  老虎机:slot_machine  抽麦序:pumping_number 存放在客户端
            duration: int 持续时间
            image_url: 'https://emoticon_images/image'
            image_small_url: 'https://emoticon_images/small'
            dynamic_image_url: '动态图'   
        }
        ...
    ]
}
```
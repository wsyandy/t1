# 表情

## 1 表情

> http-get ```/api/emoticon_images```

##### 1.1 请求参数说明

```
   公共参数
```

##### 1.2 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'  
    total_page: 3
    current_page: 1
    total_entries: 30   
    emoticon_images: [
        {
            id:  1
            name: '表情名称'
            code: '表情'
            duration: '持续时间'
            image_url: 'https://emoticon_images/image'
            image_small_url: 'https://emoticon_images/small'
            dynamic_image_url: '动态图'
            
        }
        ...
    ]
}
```
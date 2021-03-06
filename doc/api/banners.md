# Banners

## 1 Banner

> http-get ```/api/banners```

##### 1.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|new|最新|int|是|1最新|
|hot|热门|int|是|1热门|
|type|banner类型|int|是|1附近的banner|

##### 1.2 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'    
    hot_banners: [
        {
            id: int 
            name: string 
            url: string
            image_url: 'https://banners/image'
            image_small_url: 'https://banners/small'
        }
        ...
    ]
    latest_banners: [
            {
                id: int 
                name: string 
                url: string
                image_url: 'https://banners/image'
                image_small_url: 'https://banners/small'
            }
            ...
     ]
    near_banners: [
                 {
                     id: int 
                     name: string 
                     url: string
                     image_url: 'https://banners/image'
                     image_small_url: 'https://banners/small'
                 }
                 ...
          ]
}
```

## 2 点击上报

> http-post ```/api/banners/click```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|id|banner的id|int|否|||

##### 返回参数说明
````
{ 
    error_code: 0
    error_reason: ''
} 
````
# Banners

## 1 Banner

> http-get ```/api/banners```

##### 1.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|page | 当前页| int |是||
|per_page | 每页个数| int |是||
|new|最新|int|是|1最新,0非最新|
|hot|热门|int|是|1热门,0非热门|

##### 1.2 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'  
    total_page: 
    current_page: 
    total_entries:   
    banners: [
        {
            id: int 
            name: string 
            type: int  1房间，2链接
            url: string
            room_id: int  
            new:  int 1热门，0非热门
            hot:   int 1最新，0非最新
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
|id|banner图id|int|否|||

##### 返回参数说明
````
{ 
    error_code: 0
    error_reason: ''
} 
````
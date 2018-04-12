# 房间主题

## 1 主题列表

> http-get ```/iapi/room_themes```

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
    room_themes: [
        {
            id: int 
            name: string 主题名称
            icon_url: string 主题图标 'https://room_themes/icon'
            theme_image_url: string 背景图
        }
        ...
    ]
}
```
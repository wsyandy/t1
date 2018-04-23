# 房间分类

### 1 获取房间分类列表
> http-get ```/api/room_categories```

#### 1.1 请求参数说明
```angular2html
公共参数
```

#### 1.2 返回参数说明
```
{
     error_code: 0/-1
     error_reason: ''
     error_url: ''
     call_no: ''  
     channel_name: ''
     channel_key: '' 发起者channel_key                  
     receiver_channel_key: '' 接受者channel_key
    room_categories:[
    {
        name:娱乐
        second_categories: [
            {
                id : 1
                name:唱歌
            }
            
            {
                id : 2
                name:陪玩
            }
        ]
    }
    
    {
        name:娱乐
        second_caterories: [
            {
                id : 1
                name:唱歌
            }
        
            {
                id : 2
                name:陪玩
            }
        ]
    } 
   ]                 
}
```
# 房间标签

### 1 获取房间标签列表
> http-get ```/api/room_tags```

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
     room_tags:[
            {
                id : 1
                name:唱歌
            }
            {
                id : 2
                name:陪玩
            }
            ...
     ]                 
}
```
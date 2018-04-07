# Countries

## 国家列表

> http-get ```/iapi/countries```

##### 请求参数说明

```
公共参数
```

##### 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'    
    countries :{
        [
            id int 国家id
            name string 国家名称
        ]
        ...
    }
}
```
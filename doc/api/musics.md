# 音乐

## 1 音乐列表

> http-get ```/api/musics```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|hot|热门歌曲|int|是|搜索热门歌曲传1|
|page|当前页码|int|否||
|per_page|每页个数|int|否||
|search_name|搜索名称|string|是|歌曲名称或者歌唱者名称

##### 返回参数说明
````
{ 
    error_code: 0/-1
    error_reason: ''
    musics [
        {
            id int 音乐id
            name string 音乐名称
            singer_name string 演唱者名称
            user_name string 上传者名称
            file_size string 文件大小
            file_url string 文件路径
        }
    ]
} 
````

## 2 下载音乐上报

> http-post ```/api/musics/down```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|id|音乐歌曲id|int|否|||

##### 返回参数说明
````
{ 
    error_code: 0
    error_reason: ''
} 
````

## 3 删除音乐

> http-post ```/api/musics/delete```

##### 请求参数说明
|参数|名称|值类型|是否可空|备注|
|---|---|---|---|---|
|id|音乐歌曲id|int|否|||

##### 返回参数说明
````
{ 
    error_code: 0
    error_reason: ''
} 
````
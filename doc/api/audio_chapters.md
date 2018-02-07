# 音频

## 1 获取音频资源

> http-get ```/api/audio_chapters```

##### 1.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|audio_id|音频ID|int|否||
|page | 当前页| int |否||
|per_page | 每页个数| int |否|||

##### 1.2 返回参数说明
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'  
    audio_chapters: [
        {
            id: int 
            name: string 音频章节名称
            audio_id: int 音频ID
            file_url: 'https://audio_chapters/file'
        }
    ]
}
```
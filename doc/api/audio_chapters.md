# 音频

## 1 表情

> http-get ```/api/audio_chapters```

##### 1.1 请求参数说明

|参数|参数名称|类型|是否可空|备注
|---|---|---|---|---
|room_id|房间ID|int|否||
|rank | 音频章节的排名| int |是||

##### 1.2 返回参数说明yin
```
{
    error_code:   0/-1  
    error_reason: '返回码说明'  
    audio_chapter: [
        {
            id: int 
            name: string 音频章节名称
            audio_id: int 音频ID
            file_url: 'https://audio_chapters/file'
            rank: int 排名
        }
    ]
}
```
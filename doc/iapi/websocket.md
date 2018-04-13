# Websocket

## 1. 获取websocket节点

> http-get ```/iapi/websocket/end_point```

##### 1.1 请求参数说明
```
公共参数
```

##### 1.2 响应参数说明
```
{
    error_code
    error_reason
    end_point:ws://ctest.yueyuewo.cn:9999
    ping_interval:心跳包时间间隔
}
```


## 2. Websocket通信细节

### 2.1 长连接断开情况
```
客户端15秒发一次心跳包 服务端20秒未收到心跳包则长连接为断开(如网络异常)
客户端杀掉进程 长连接直接断开
```

### 2.2 网络重新连接处理
```
用户网络重连后获取一次用户基本信息,如果发现用户的current_room_id为空则表示用已被踢出房间，
此时客户端应让用户退出声网的房间(无需调用服务端退出房间接口)
```

### 2.3 杀掉进程处理
```
杀掉进程后服务端会把用户直接踢出房间
```

### 2.4 断线通知
```
用户A断线后服务端会把用户A直接踢出房间,并通知此房间的另一位用户B,由用户B通过信令通知房间的其他用户
用户A已退出房间;用户B完成通知后需上报服务器已通知(防止用户B也出现异常)
```

## 3. websocket通信结构
 
 ### 3.1 客户端请求服务端消息结构
 #### 心跳包结构
 ```
 {
    action:ping
    online_token:xxxxx websocket链接时由服务端生成返回给客户端
    timestamp:xxxxxx  时间戳
    sid
    sign:xxxxxx  签名
 }
 ```
 
 #### 退出房间上报
   ```
   {
      action:exit_room_report 退出房间上报成功
      user_id:1233 退出房间的用户id
      online_token:xxxxx websocket链接时由服务端生成返回给客户端
      sid 上报的用户sid
      timestamp:xxxxxx  时间戳
      sign:xxxxxx  签名
   }
   ```
 
 #### 挂断电话上报
 ```
 {
    action: hang_up_report 挂断电话上报成功
    user_id:  挂断电话用户id
    online_token: websocket 链接时由服务端生成返回给客户端
    sid 上报的用户sid
    timestamp: 时间戳
    sign： 签名
 }
 ```
   
 #### 进入房间上报
   ```
   {
      action:enter_room_report 进入房间上报成功
      user_id:1233 进入房间的用户id
      online_token:xxxxx websocket链接时由服务端生成返回给客户端
      sid  上报的用户sid
      timestamp:xxxxxx  时间戳
      sign:xxxxxx  签名
   }
   ```  
   
   #### 发公屏消息上报
   ```
   {
      action:send_topic_msg_report 发公屏消息上报成功
      user_id:1233 发公屏消息的用户id
      online_token:xxxxx websocket链接时由服务端生成返回给客户端
      sid   上报的用户sid
      timestamp:xxxxxx  时间戳
      sign:xxxxxx  签名
   }
   ```  
     
   #### 送礼物上报
   ```
   {
      action:send_gift_report 送礼物上报成功
      user_id:1233 送礼物的用户id
      online_token:xxxxx websocket链接时由服务端生成返回给客户端
      sid   上报的用户sid
      timestamp:xxxxxx  时间戳
      sign:xxxxxx  签名
   }
   ```
   
  #### 上麦上报
   ```
   {
      action:up_deport 上麦上报成功
      user_id:1233 上麦的用户id
      online_token:xxxxx websocket链接时由服务端生成返回给客户端
      sid   上报的用户sid
      timestamp:xxxxxx  时间戳
      sign:xxxxxx  签名
   }
   ```  
     
 #### 下麦上报
   ```
   {
      action:down_report 下麦上报成功
      user_id:1233 下麦的用户id
      online_token:xxxxx websocket链接时由服务端生成返回给客户端
      sid   上报的用户sid
      timestamp:xxxxxx  时间戳
      sign:xxxxxx  签名
   }
   ```   
 
### 3.2 服务端通知客户端的消息结构
 #### 退出房间
  ```
  {
     action:exit_room 退出房间 (由于网络异常或进程退出导致的退出房间)   
     user_id:1233 退出房间的用户id
     channel_name 房间频道
     room_seat:{
        id 麦位id
        status 麦位状态
        microphone 麦位麦克风状态
     } //退出房间用户所在的麦位
  }
  ```
 #### 挂断电话
```
    {
        action:hang_up 挂断电话(由于网络异常或进程退出导致的电话中断)
        user_id 挂断电话的用户id
        receiver_id 对方用户id
        channel_name 房间频道
    }
``` 
  
  #### 进入房间
```
    {
        action:enter_room 进入房间
        user_id 进入房间的用户id
        nickname 进入房间的用户昵称
        sex 进入房间的用户性别
        avatar_url 进入房间的用户的头像
        avatar_small_url 进入房间的用户头像小图
        channel_name 房间频道
        i_segment 段位 新人（newcomer）人才（talented）专家（professional）明星（stars）百万富翁（millionaire） VIP（VIP）
        i_segment_text 段位文本
        user_car_gift: {
            name: ''
            image_url: ''
            image_small_url: ''
            image_big_url: ''
            dynamic_image_url: ''
            svga_image_name: svga 对应zip包中的图片名称
            render_type 渲染类型 gif svga
            svga_image_url svga 图片
            show_rank int 礼物展示排序
            expire_time int 礼物过期时间
            gift_type 1 普通礼物 2 座驾
            notice_content 进房间提示文案
        }
    }
```  
  
  #### 发公屏消息 
```
    {
        action:send_topic_msg 发送公屏消息
        user_id 发送消息的用户id
        nickname 发送消息的用户昵称
        sex 发送消息的用户性别
        avatar_url 发送消息间的用户的头像
        avatar_small_url 发送消息的用户头像小图
        content 消息内容
        channel_name 房间频道
    }
```  
    
  #### 送礼物
```
    {
        action: send_gift 送礼物      
        notify_type bc 通知类型bc广播 ptp点对点
        channel_name 房间频道
        gift: {
            id 礼物id
            sender_room_seat_id 发送者的麦位id
            receiver_room_seat_id 接收者的麦位id
            sender_id 发送者id
            receiver_id 接收者id
            sender_nickname 发送者昵称
            receiver_nickname 接收者昵称
            name 礼物名称
            dynamic_image_url 动图
            image_url 静态图
            image_big_url 静态大图
            image_small_url 静态小图
            num 礼物个数
        }
    }
```   
  
   #### 上麦
```
    {
        action: up 上麦
        channel_name 房间频道
        room_seat{
            user_id 上麦的用户id
            id 麦位id
            sex 性别
            avatar_url 用户头像
            avatar_small_url 用户小图 
            nickname 用户昵称
            status 麦位状态
            microphone 麦克风状态
            rank 麦序
            room_id 房间id
        }
    }
```   
    
   #### 下麦
```
    {
        action: down 下麦
        channel_name 房间频道
        room_seat{
            id 麦位id 
            status 麦位状态
            microphone 麦克风状态
            rank 麦序
            room_id 房间id
        }
    }
```   

   #### 房间通知信息
```
    {
        action: room_notice 房间通知信息
        channel_name 房间频道
        expire_time 停留时间 单位:秒
        content: string "" 信息内容
        client_url string 跳转地址 例app://rooms/detail?id=xxx
    }
```   
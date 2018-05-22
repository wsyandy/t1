<?php

class Games extends BaseModel
{

    static $STATUS = [STATUS_OFF => '无效', STATUS_ON => '有效'];
    static $files = ['icon' => APP_NAME . '/games/icon/%s'];

    static function getGatewayClasses()
    {
        return \gamegateway\Base::getGatewayNames();
    }

    function gateway()
    {
        $clazz = '\gamegateway\\' . $this->clazz;
        $gateway = new $clazz($this);

        return $gateway;
    }

    function getIconUrl($size = null)
    {
        if (isBlank($this->icon)) {
            return null;
        }
        $url = StoreFile::getUrl($this->icon);
        if ($size) {
            $url .= "@!" . $size;
        }
        return $url;
    }

    function getIconSmallUrl()
    {
        return $this->getIconUrl('small');
    }

    function mergeJson()
    {
        return [
            'icon_small_url' => $this->icon_small_url,
        ];
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon_url' => $this->icon_url,
            'icon_small_url' => $this->icon_small_url,
            'url' => $this->url
        ];
    }

    static function sendGameMessage($current_user, $body)
    {

        $intranet_ip = $current_user->getIntranetIp();
        $receiver_fd = $current_user->getUserFd();

        $result = \services\SwooleUtils::send('push', $intranet_ip, \Users::config('websocket_local_server_port'), ['body' => $body, 'fd' => $receiver_fd]);
        info('推送结果=>', $result);
    }

    function generateGameClientUrl($current_user, $room, $game_history)
    {
        $is_host = $current_user->isRoomHost($room);
        $site = $current_user->current_room_seat_id;
        $owner = 1;
        if ($is_host) {
            $site = 1;
            $owner = 0;
        }

        if ($game_history->status == GAME_STATUS_PLAYING) {
            $site = 0;
        }

        $client_url = $this->url . '?sid=' . $current_user->sid . '&code=' . $current_user->product_channel->code . '&game_id=' . $this->id .
            '&name=' . $this->name . '&username=' . $current_user->nickname . '&room_id=' . $room->id . '&user_id=' . $current_user->id .
            '&avater_url=' . $current_user->avatar_url . '&user_num_limit=8&site=' . $site . '&owner=' . $owner . '&game_history_id=' . $game_history->id;

        info('拼接跳转到游戏的链接', $client_url);

        return $client_url;
    }
}
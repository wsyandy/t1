<?php

class Provinces extends BaseModel
{
    static function findByIp($ip)
    {
        $data = \Provinces::ipLocation($ip);
        if (is_array($data)) {
            if (!isset($data[0]) || !preg_match('/中国/', $data[0])) {
                return null;
            }

            if (isset($data[1]) && $data[1]) {
                $province = self::findFirstByName($data[1]);
                return $province;
            }
        }

        return null;
    }

    static function ipLocation($ip)
    {

        $config = self::di('config');
        $endpoint = $config->job_queue->endpoint;
        $x_redis = XRedis::getInstance($endpoint);

        $key = 'ip_location_data_' . $ip;
        $data = $x_redis->get($key);
        if ($data) {
            return json_decode($data, true);
        }

        $data = \IPLocation::find($ip);
        if (is_array($data)) {
            $x_redis->setex($key, 3600 * 24, json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        return $data;
    }

    static function findIpPosition($ip)
    {
        $result = self::ipLocation($ip);
        if (is_string($result)) {
            return $result;
        }

        return implode(',', array_filter($result));
    }

    static function findIpProvince($ip)
    {
        $result = self::ipLocation($ip);
        if (is_array($result) && isset($result[1])) {
            return $result[1];
        }

        return false;
    }

    static function findFirstByName($name)
    {
        if (!is_string($name)) {
            warn("Exce", $name);
            return null;
        }

        if (preg_match('/市$/u', $name)) {
            $name = preg_replace('/市$/u', '', $name);
        }

        if (preg_match('/省$/u', $name)) {
            $name = preg_replace('/省$/u', '', $name);
        }

        return parent::findFirstByName($name);
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}
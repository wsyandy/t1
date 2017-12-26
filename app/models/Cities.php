<?php

class Cities extends BaseModel
{
    /**
     * @type Provinces
     */
    private $_province;

    static function findByIp($ip)
    {
        $data = \Provinces::ipLocation($ip);
        if (is_array($data)) {
            if (!isset($data[0]) || !preg_match('/中国/', $data[0])) {
                return null;
            }

            if (isset($data[2]) && $data[2]) {
                $city = self::findFirstByName($data[2]);
                return $city;
            }
        }

        return null;
    }

    static function findFirstByName($name)
    {

        $preg_str = '/(傣族自治|彝族自治|藏族自治|蒙古自治|蒙古族|哈尼族|朝鲜族自治|回族自治|白族自治|苗族侗族自治|' .
            '布依族苗族自治|土家族苗族自治|哈萨克自治|壮族苗族自治|傈僳族自治|傣族景颇族自治|柯尔克孜自治|藏族羌族自治|黎族自治)/u';

        $name = preg_replace($preg_str, '', $name);

        $city = parent::findFirstByName($name);
        if ($city) {
            return $city;
        }

        if (!preg_match('/市$/', $name)) {
            $city = parent::findFirstByName($name . '市');
            if ($city) {
                return $city;
            }
        } else {
            $city = parent::findFirstByName(preg_replace('/市$/', '', $name));
            if ($city) {
                return $city;
            }
        }

        if (!preg_match('/州$/', $name)) {
            $city = parent::findFirstByName($name . '州');
            if ($city) {
                return $city;
            }
        }

        if (!preg_match('/地区$/', $name)) {
            $city = parent::findFirstByName($name . '地区');
            if ($city) {
                return $city;
            }
        }

        return null;
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }


    static function getAllCities()
    {
        $res = [];
        $provinces = \Provinces::findForeach(['order' => 'id asc']);
        foreach ($provinces as $province) {
            $province_json = $province->toSimpleJson();
            $province_json['child'] = [];
            $cities = \Cities::findByProvinceId($province->id);
            foreach ($cities as $city) {
                $city_json = $city->toSimpleJson();
                $province_json['child'][] = $city_json;
            }

            $res[] = $province_json;
        }
        return $res;
    }

}
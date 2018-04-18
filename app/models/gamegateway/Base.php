<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/4/18
 * Time: 下午9:07
 */

namespace gamegateway;

class Base
{

    function __construct($game)
    {

    }

    public static function getGatewayNames()
    {
        $names = array();
        $filenames = glob(__DIR__ . '/*.php');
        $project_filenames = glob(APP_ROOT . 'app/models/gamegateway/*.php');
        $filenames = array_merge($filenames, $project_filenames);

        foreach ($filenames as $filename) {
            $name = basename($filename, '.php');
            if ('Base' != $name) {
                $names[$name] = $name;
            }
        }

        return $names;
    }
}
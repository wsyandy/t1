<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 17:35
 */

class ImNotify extends BaseModel
{
    static $_only_cache = true;

    /**
     * @param $model
     * @param $action
     * @param $notify_type
     * @param $opts
     * @return array
     */
    static function generateNotifyData($model, $action, $notify_type, $opts)
    {
         $clazz = '\\' . \Phalcon\Text::camelize($model);
         $data = $clazz::generateNotifyData($opts);
         return array(
             'model' => $model,
             'action' => $action,
             'notify_type' => $notify_type,
             'timestamp' => time(),
             'data' => $data
         );
    }
}
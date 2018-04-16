<?php

class SinaAdConfigs extends BaseModel
{
    /**
     * @type Operators
     */
    private $_operator;

    static $PLATFORM = [USER_PLATFORM_ANDROID => '安卓', USER_PLATFORM_IOS => '苹果'];

    function mergeJson()
    {
        return ['operator_username' => $this->operator_username];
    }

}
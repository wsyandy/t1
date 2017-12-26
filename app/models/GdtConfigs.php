<?php

class GdtConfigs extends BaseModel
{
    /**
     * @type Operators
     */
    private $_operator;

    static $PLATFORM = ['android' => '安卓', 'ios' => 'ios'];

    function mergeJson()
    {
        return ['operator_username' => $this->operator_username];
    }

}
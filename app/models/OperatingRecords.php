<?php

class OperatingRecords extends BaseModel
{
    /**
     * @type Operators
     */
    private $_operator;

    static $ACTION_TYPE = ['create' => '创建', 'update' => '更新', 'delete' => '删除'];

    function mergeJson()
    {
        return ['operator_username' => $this->operator_username];
    }

    static function logAfterCreate($operator, $target)
    {
        self::log($operator, $target, $action_type = 'create');
    }

    static function logBeforeUpdate($operator, $target)
    {
        self::log($operator, $target, $action_type = 'update');
    }

    static function logBeforeDelete($operator, $target)
    {
        self::log($operator, $target, $action_type = 'delete');
    }

    // create 创建后打点记录
    // update 更新保存前打点记录
    // delete 删除前打点记录
    static function log($operator, $target, $action_type = 'update')
    {

        $clazz = get_class($target);
        $operating_record = new OperatingRecords();
        $operating_record->operator_id = $operator->id;
        $operating_record->table_name = $clazz;
        $operating_record->target_id = $target->id;
        $operating_record->action_type = $action_type;

        $results = [];
        // 具体操作分析
        if (in_array($action_type, ['update'])) {
            $reflection_class = new ReflectionClass($clazz);
            foreach ($target->toData() as $k => $v) {
                if ($target->hasChanged($k)) {

                    $static_field = strtoupper($k);
                    $text_values = $reflection_class->getStaticPropertyValue($static_field, null);

                    $old_value = $target->was($k);
                    if ($text_values && array_key_exists($v, $text_values)) {
                        $v_text = fetch($text_values, $v);
                        $old_text = fetch($text_values, $old_value);
                    } else {
                        $v_text = $v;
                        $old_text = $old_value;
                    }

                    $results[] = '修改:' . $k . ', 从: ' . $old_text . ', 到: ' . $v_text;
                }
            }
        }

        if ($results) {
            $operating_record->data = json_encode($results, JSON_UNESCAPED_UNICODE);
        }

        $operating_record->save();
    }

    static function getTableNames()
    {
        $names = [];
        foreach (glob(APP_ROOT . 'app/models/*.php') as $filename) {
            $basename = basename($filename);
            $basename = substr($basename, 0, -4);
            $names[$basename] = $basename;
        }
        return $names;

    }

}
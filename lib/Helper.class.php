<?php

final class Helper {

    /**
     * 对变量进行 JSON 编码
     * @summery json_encode ( $value, JSON_UNESCAPED_UNICODE )的兼容写法
     * @param mixed value 待编码的 value ，除了resource 类型之外，可以为任何数据类型，该函数只能接受 UTF-8 编码的数据
     * @return string 返回 value 值的 JSON 形式
     */
    public static function _json_encode_ex($value) {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $str = json_encode($value);
            $str = preg_replace_callback("#\\\u([0-9a-f]{4})#i", function ($matchs) {
                return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs [1]));
            }, $str);
            return $str;
        } else {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 兼容低版本array_column功能
     * @param array $data
     * @param string $columnKey
     * @param string $indexKey default null
     * @return multitype:Ambigous <NULL, mixed, unknown>
     */
    public static function i_array_column($data, $columnKey, $indexKey = null) {
        if (!function_exists('array_column')) {
            $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
            $indexKeyIsNull = (is_null($indexKey)) ? true : false;
            $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;
            $result = array();
            foreach ((array) $data as $key => $row) {
                if ($columnKeyIsNumber) {
                    $tmp = array_slice($row, $columnKey, 1);
                    $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
                } else {
                    $tmp = isset($row [$columnKey]) ? $row [$columnKey] : null;
                }
                if (!$indexKeyIsNull) {
                    if ($indexKeyIsNumber) {
                        $key = array_slice($row, $indexKey, 1);
                        $key = (is_array($key) && !empty($key)) ? current($key) : null;
                        $key = is_null($key) ? 0 : $key;
                    } else {
                        $key = isset($row [$indexKey]) ? $row [$indexKey] : 0;
                    }
                }
                $result [$key] = $tmp;
            }
            return $result;
        } else {
            return array_column($data, $columnKey, $indexKey);
        }
    }

    public static function getTimeStampString($format = "Y-m-d H:i:s") {
        return date($format);
    }

}

<?php

namespace common\components\alipay\requests;

use yii\base\Exception;

class Request
{
    private $_params = [];

    public $params = [];

    public $method = '';

    /**
     * @param $name
     * @param $value
     * @throws Exception
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->params)) {
            $this->_params[$name] = $value;
        } else {
            throw new Exception('不存在的字段赋值：' . $name);
        }
    }

    /**
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->params)) {
            return $this->_params[$name];
        } else {
            throw new Exception('不存在的字段读取：' . $name);
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getApiParams()
    {
        $this->check();
        return $this->_params;
    }

    /**
     * 检查参数
     * @throws Exception
     */
    private function check()
    {
        foreach ($this->params as $key => $rules) {
            if (empty($rules) || !is_array($rules)) {
                continue;
            }
            foreach ($rules as $rule) {
                switch ($rule) {
                    case 'require':
                        if (empty($this->_params[$key])) {
                            throw new Exception($key . '不能为空');
                        }
                        break;
                    case 'string':
                        if (!is_string($this->_params[$key])) {
                            throw new Exception($key . '必须为字符串');
                        }
                        break;
                    case 'boolean':
                        if (!is_bool($this->_params[$key])) {
                            throw new Exception($key . '必须为Bool类型');
                        }
                        break;
                    default:
                        throw new Exception($key . '未知类型');
                }
            }
        }
    }
}

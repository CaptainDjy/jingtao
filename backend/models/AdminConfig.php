<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/21
 * Time: 10:40
 */

namespace backend\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


class AdminConfig extends ActiveRecord
{
    public function rules()
    {
        return [
            //验证不能为空
            [['extra', 'remark', 'type'], 'safe'],
            [['name'], 'match', 'pattern' => '/^[A-Z_]*$/', 'message' => '配置标识不合要求'],
            [['name', 'title', 'groups', 'value'], 'required'],
            [['sort', 'status'], 'integer', 'message' => '必须为整数'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '配置标识',
            'title' => '标题',
            'type' => '配置类型',
            'groups' => '配置分组',
            'value' => '配置值',
            'sort' => '排序',
            'extra' => '配置项',
            'status' => '是否显示',
            'remark' => '说明',
        ];
    }


    /**
     * 根据配置类型解析配置
     * @param integer $type 配置类型
     * @param string $value 配置值
     * @return array
     */
    private function parse($type, $value)
    {
        switch ($type) {
            case 3: //解析数组
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if (strpos($value, ':')) {
                    $value = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k] = $v;
                    }
                } else {
                    $value = $array;
                }
                break;
        }
        return $value;
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }


}

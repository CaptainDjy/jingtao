<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/31
 * Time: 11:05
 */
namespace backend\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Cooperationuser extends ActiveRecord{
    public function rules(){
        return[
            [['uid','cycle'],'required'],
            [['uid','cycle'], 'integer', 'message' => '必须为整数'],
            [['price'],'number']
        ];

    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cycle' => '周期',
            'price' => '价格',
            'status'=>'状态',
        ];
    }

    public static function initModel($id = null)
    {
        if (!empty($id)) {
            return self::findOne($id);
        }
        $model = new self();
        $model->loadDefaultValues();
        return $model;
    }
}
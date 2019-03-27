<?php
namespace common\models;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
class Cooperationuser extends ActiveRecord{
    public function rules()
    {
        return [
            [['uid'], 'required'],
//            [['trade_sn'], 'required', 'on' => 'trade_sn'],
            [['uid', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }
}
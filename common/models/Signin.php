<?php
namespace common\models;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
class Signin extends ActiveRecord{
    public static function tableName()
    {
        return '{{%signin}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
        ];
    }
}
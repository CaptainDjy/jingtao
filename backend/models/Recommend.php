<?php
namespace backend\models;
use yii\db\ActiveRecord;
use yii;
/**
*模型
*/
class Recommend extends ActiveRecord {

    public function rules()
    {
        return [
            [['sole'], 'required','message' => '不能为空'],
            [['sole'], 'number','message' => '为数字'],
        ];
    }
}
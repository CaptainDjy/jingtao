<?php
namespace api\models;
use yii\db\ActiveRecord;
/**
*用户模型
*/
class Recommend extends ActiveRecord {

    public function rules(){
        return [
            [['agency', 'sole'], 'required'],
        ];
    }
}
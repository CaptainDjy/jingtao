<?php
namespace common\models;
use yii\db\ActiveRecord;
class Commissionset extends ActiveRecord {

    public function rules() {
        return [

            [['detain','stair','second','threelevel','zghy','zgdl','zgzd'],'number']
        ];
    }

}
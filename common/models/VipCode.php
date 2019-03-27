<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property int $id [int(10) unsigned]
 * @property int $enabled [tinyint(1)] 是否启用 1=启用 0=不启用
 * @property string $code [varchar(50)]  名称
 * @property int $price [int(11) unsigned]  价格
 * @property int $uid [int(11) ]  使用者id
 */
class VipCode extends ActiveRecord
{

    public function rules()
    {
        return [];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => '生成码',
            'price' => '金额',
            'enabled' => '是否使用',
            'uid' => '使用者id',
        ];
    }
}

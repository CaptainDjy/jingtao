<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property int $id [int(10) unsigned]
 * @property int $lv [tinyint(1)] 等级
 * @property int $enable [tinyint(1)] 是否启用 1=启用 0=不启用
 * @property string $name [varchar(255)]  名称
 * @property int $price [int(11) unsigned]  价格
 * @property int $sort [tinyint(2) unsigned]  权重，越大越靠前
 * @property int $indate [int(10) 0]  会员有效期（天）
 */
class VipList extends ActiveRecord
{

    public function rules()
    {
        return [];
    }

    public function attributeLabels()
    {
        return [
            'id' => '操作编号',
            'lv' => '等级',
            'price' => '价格',
            'name' => '名称',
            'sort' => '权重',
        ];
    }
}

<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "dh_robot_ddk".
 *
 * @property string $id
 * @property string $title [varchar(255)]  采集名称
 * @property int $from_cid [int(11)]  来源分类
 * @property int $to_cid [int(11)]  入库分类
 * @property int $created_at [int(11) unsigned]  创建时间
 * @property int $updated_at [int(11) unsigned]  更新时间
 */
class RobotDdk extends ActiveRecordBase
{
    const FROM_CATEGORY = [
        1 => '食品',
        4 => '母婴',
        12 => '海淘',
        13 => '水果',
        14 => '女装',
        15 => '百货',
        16 => '美妆',
        18 => '电器',
        590 => '充值',
        743 => '男装',
        818 => '家纺',
        1281 => '鞋包',
        1282 => '内衣',
        1451 => '运动',
        1543 => '手机',
        1917 => '家装',
        2048 => '汽车',
        2478 => '电脑',

    ];

    public static function map()
    {
        $model = self::find()->select(['from_cid', 'to_cid'])->asArray()->all();
        return ArrayHelper::map($model, 'from_cid', 'to_cid');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'from_cid', 'to_cid'], 'required'],
            [['from_cid', 'to_cid', 'created_at', 'updated_at'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '采集名称',
            'from_cid' => '来源分类',
            'to_cid' => '入库分类',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
        ];
    }
}

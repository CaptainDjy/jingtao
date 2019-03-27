<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "dh_robot_jd".
 *
 * @property string $id
 * @property string $title [varchar(255)]  采集名称
 * @property int $from_cid [int(11)]  来源分类
 * @property int $to_cid [int(11)]  入库分类
 * @property int $created_at [int(11) unsigned]  创建时间
 * @property int $updated_at [int(11) unsigned]  更新时间
 */
class RobotJd extends ActiveRecord
{
    const FROM_CATEGORY = [
        652 => '数码',
        670 => '电脑、办公',
        737 => '家用电器',
        1320 => '食品饮料',
        1315 => '服饰内衣',
        1316 => '美妆护肤',
        1318 => '运动户外',
        1319 => '母婴',
        1620 => '家居日用',
        1713 => '图书',
        1672 => '礼品箱包',
        4051 => '音乐',
        4052 => '影视',
        4053 => '教育音像',
        4938 => '本地生活/旅游出行',
        5025 => '钟表',
        5272 => '数字内容',
        6196 => '厨具',
        6144 => '珠宝首饰',
        6233 => '玩具乐器',
        6994 => '宠物生活',
        6728 => '汽车用品',
        9192 => '医药保健',
        9855 => '家装建材',
        9847 => '家具',
        9987 => '手机',
        9259 => '测试分类',
        11729 => '鞋靴',
        12218 => '生鲜',
        12259 => '酒类',
        12367 => '团购',
        12379 => '整车',
        12473 => '农资绿植',
        13314 => '处方药',
        13678 => '教育培训',
        13765 => '二手商品',
        13887 => '邮币',
        13996 => 'IP',
        15083 => '房地产',
        15126 => '艺术品',
        15248 => '家纺',
        15901 => '家庭清洁/纸品',
        15980 => '京东服务',
        16750 => '个人护理',
        17329 => '包包',
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
            'from_cid' => '采集ID',
            'to_cid' => '入库ID',
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

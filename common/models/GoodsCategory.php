<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%goods_category}}".
 *
 * @property int $id ID
 * @property string $title 产品分类
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property string $img [varchar(255)]  图标
 * @property int $sort [int(10) unsigned]  排序
 */
class GoodsCategory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_category}}';
    }

    /**
     * @return array
     */
    public static function map()
    {
        $model = self::find()->select(['id', 'title'])->asArray()->all();
        if (empty($model)) {
            return [];
        }
        $map = ArrayHelper::map($model, 'id', 'title');
        return $map;
    }

    /**
     * 初始化模型
     * @param $id int
     * @return Goods|null|static
     */
    public static function initModel($id = null)
    {
        if (!empty($id)) {
            return self::findOne($id);
        }
        $model = new self();
        $model->loadDefaultValues();
        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['created_at', 'updated_at', 'sort'], 'integer'],
            [['title', 'img'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '分类名称',
            'img' => '分类图标',
            'sort' => '排序',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    public function getGoods()
    {
        return $this->hasMany(Goods::className(),['cid' => 'id'])/*->limit(3)*/;
    }
}

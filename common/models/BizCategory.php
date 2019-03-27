<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%biz_category}}".
 *
 * @property int $id ID
 * @property string $title 产品分类
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property string $pic [varchar(255)]  分类图标
 */
class BizCategory extends ActiveRecord
{
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
    public static function tableName()
    {
        return '{{%biz_category}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['title', 'pic'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pic' => '分类图标',
            'title' => '商家分类',
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
}

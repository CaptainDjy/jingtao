<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%biz}}".
 *
 * @property int $id
 * @property string $cid 商家分类
 * @property string $title 商家
 * @property string $img 商家
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Biz extends ActiveRecord
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
     * @return \yii\db\ActiveQuery
     */
    public function getBizCategory()
    {
        return $this->hasOne(BizCategory::class, ['id' => 'cid']);
    }


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%biz}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cid', 'title'], 'required'],
            [['cid', 'created_at', 'updated_at'], 'integer'],
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
            'cid' => '商家分类',
            'title' => '商家名称',
            'img' => '店铺缩略图',
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

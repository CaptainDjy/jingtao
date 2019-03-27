<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%advert}}".
 *
 * @property string $id
 * @property string $title 标题
 * @property string $img 广告图
 * @property string $url 跳转链接
 * @property int $deadline 结束时间
 * @property int $status 状态 0隐藏 1显示
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 * @property int $position_id [int(11)]  广告位
 */
class Advert extends ActiveRecord
{
    const STATUS_LABEL = [
        1 => '显示',
        0 => '隐藏',
    ];

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
        return '{{%advert}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['position_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['title'], 'required'],
            [['title', 'img', 'url'], 'string', 'max' => 255],
            [['deadline'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'position_id' => '广告位',
            'title' => '广告标题',
            'img' => '广告图',
            'url' => '跳转链接',
            'deadline' => '结束时间',
            'status' => '状态',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvertPosition()
    {
        return $this->hasOne(AdvertPosition::class, ['id' => 'position_id']);
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

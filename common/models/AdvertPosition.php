<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%advert_position}}".
 *
 * @property string $id
 * @property string $title 标题
 * @property int $status 状态 0隐藏 1显示
 * @property string $remark 备注
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 * @property string $img [varchar(255)]  图片
 * @property string $op [varchar(50)]  标识
 * @property int $width [int(10) unsigned]  宽度
 * @property int $height [int(10) unsigned]  高度
 */
class AdvertPosition extends ActiveRecord
{
    const STATUS_LABEL = [
        0 => '隐藏',
        1 => '显示',
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
        return '{{%advert_position}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'op', 'width', 'height'], 'required'],
            [['width', 'height', 'status', 'created_at', 'updated_at'], 'integer'],
            [['remark'], 'string'],
            [['title', 'img'], 'string', 'max' => 255],
            [['op'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'img' => '图片',
            'op' => '标识',
            'width' => '宽度',
            'height' => '高度',
            'status' => '状态',
            'remark' => '备注',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
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

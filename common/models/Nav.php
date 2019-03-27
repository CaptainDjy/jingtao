<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%nav}}".
 *
 * @property string $id
 * @property string $title 标题
 * @property string $img 图标
 * @property string $url 跳转链接
 * @property string $sort 排序
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 * @property bool $type [tinyint(1)]  1主页2淘宝3京东4拼多多
 */
class Nav extends ActiveRecord
{
    const TYPE_LABEL = [
        1 => '主页',
        2 => '淘宝',
        3 => '京东',
        4 => '拼多多',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%nav}}';
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
            [['type', 'sort', 'created_at', 'updated_at'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['img', 'url'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'img' => '图标',
            'url' => '跳转链接',
            'sort' => '排序',
            'type' => '位置',
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

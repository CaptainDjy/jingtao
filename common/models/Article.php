<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%article}}".
 *
 * @property string $id
 * @property string $cid 文章分类
 * @property string $title 文章标题
 * @property string $description 简述
 * @property string $img 缩略图
 * @property string $small_img 缩略小图
 * @property string $content 文章内容
 * @property string $url 跳转链接
 * @property int $status 状态，0隐藏1显示
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 */
class Article extends ActiveRecord
{
    const STATUS_LABEL = [
        1 => '显示',
        0 => '隐藏',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article}}';
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
     * @return \yii\db\ActiveQuery
     */
    public function getArticleCategory()
    {
        return $this->hasOne(ArticleCategory::class, ['id' => 'cid']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cid', 'status', 'created_at', 'updated_at'], 'integer'],
            [['title'], 'required'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['description', 'img', 'url', 'small_img'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cid' => '文章分类',
            'title' => '文章标题',
            'description' => '文章简述',
            'img' => '缩略图',
            'small_img' => '缩略小图',
            'content' => '文章内容',
            'url' => '跳转链接',
            'status' => '状态',
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

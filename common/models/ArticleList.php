<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "chain_article_list".
 *
 * @property string $id
 * @property string $title
 * @property string $pic
 * @property integer $cid
 * @property string $content
 * @property string $excerpt
 * @property integer $status
 * @property integer $hits
 * @property string $created_at
 * @property string $updated_at
 * @property string $article_url
 * @property string $sort
 */
class ArticleList extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 0;
    const STATUS_DELETED = 9;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%article_list}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['excerpt', 'content'], 'string'],
            [['status', 'hits', 'created_at', 'updated_at', 'sort', 'cid'], 'integer'],
            [['title', 'pic', 'article_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '文章标题',
            'pic' => '幻灯图',
            'content' => '文章内容',
            'excerpt' => '文章摘要',
            'status' => '状态',
            'hits' => '点击量',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'article_url' => '链接地址',
            'sort' => '排序',
            'cid' => '上级ID'
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

//    /**
//     * 置顶状态改变
//     * @param $id
//     * @return bool
//     */
//    public static function changeTop($id){
//        $model = new self();
//        $item = $model::find()->where("istop = 1 and cid != 27")->one();
//        if(!empty($item)){
//            $item->istop = 0;
//            $item->updated_at = time();
//            $item->save(false);
//        }
//        $item1 = $model::findOne($id);
//        if($item1->istop == 0){
//            $item1->istop = 1;
//        }else{
//            $item1->istop = 0;
//        }
//        $item1->updated_at = time();
//        $result1 = $item1->save(false);
//        if(!empty($result1)){
//            return true;
//        }else{
//            return false;
//        }
//    }
}

<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class Announcement
 * @package common\models
 * @property int $id [int(11) unsigned]  公告id
 * @property string $title [varchar(255)]  标题
 * @property string $remark 描述
 * @property string $thumb [varchar(100)]
 * @property string $content 内容
 * @property bool $status [tinyint(1) unsigned]  状态，1启用，9禁用
 * @property int $type [smallint(3) unsigned]  类型，1系统
 * @property bool $heat [tinyint(1) unsigned]  热度状态
 * @property int $created_at [int(11) unsigned]
 * @property int $updated_at [int(11) unsigned]
 */
class Announcement extends ActiveRecord
{
    public function rules()
    {
        return [
            [['title'], 'required', 'message' => '标题不能为空'],
            [['remark'], 'required', 'message' => '描述不能为空'],
            //[['content'],'required','message'=>'内容不能为空'],
            [['type', 'status', 'content', 'thumb', 'heat', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '公告标题',
            'remark' => '公告描述',
            'content' => '公告内容',
            'type' => '公告类型',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'heat' => '热度状态',
            'status' => '状态',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }
}

<?php

namespace common\models;


use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%message}}".
 *
 * @property int $id 序号
 * @property int $uid 用户UID
 * @property string $text 内容
 * @property int $created_at 更新时间
 * @property int $status 更新时间
 * @property int $type 更新时间
 * @property int $updated_at 创建时间
 */
class Message extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%message}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uid', 'text',], 'required'],
            [['id', 'uid', 'status', 'type', 'created_at', 'updated_at'], 'integer'],
            [['text'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'text' => 'Text',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * 添加订单
     * @param $data
     * @return int
     * @throws \yii\db\Exception
     */
    public static function addOrder($data)
    {
        $result = \Yii::$app->db->createCommand()->batchInsert(self::tableName(), ['uid', 'text','created_at','updated_at'], $data)->execute();
        return $result;
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
        ];
    }
}

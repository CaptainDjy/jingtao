<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%log}}".
 *
 * @property int $id
 * @property string $op
 * @property string $msg
 * @property int $created_at
 * @property int $updated_at
 */
class Log extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['msg'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['op'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function add($data, $op = 'sys')
    {
        $model = new self();
        $model->loadDefaultValues();
        $model->op = $op;
        $model->msg = json_encode($data,JSON_UNESCAPED_SLASHES);
        $model->save();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'op' => 'Op',
            'msg' => 'Msg',
            'created_at' => 'Created At',
            'updated_at' => 'updated_at',
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

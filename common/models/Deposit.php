<?php

namespace common\models;


use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%deposit}}".
 *
 * @property int $id
 * @property int $uid
 * @property string $price
 * @property int $created_at
 * @property int $updated_at
 */
class Deposit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%deposit}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'required'],
            [['uid', 'created_at', 'updated_at'], 'integer'],
            [['price'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '序号',
            'uid' => '用户ID',
            'price' => '沉淀金额',
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
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * 添加记录
     * @param $uid
     * @param $price
     * @return bool
     */
    public static function add($uid, $price)
    {
        $model = new self();
        $model->uid = $uid;
        $model->price = $price;
        if ($model->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 计算总和
     * @return mixed
     */
    public static function sumPrice()
    {
        return self::find()->asArray()->sum('price');
    }

}

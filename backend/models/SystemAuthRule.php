<?php

namespace backend\models;


use yii\db\ActiveRecord;

/**
 * This is the model class for table "th_system_auth_rule".
 *
 * @property string $name
 * @property resource $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property SystemAuthItem[] $systemAuthItems
 */
class SystemAuthRule extends ActiveRecord
{


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['data'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '规则名称',
            'data' => '规则类名',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSystemAuthItems()
    {
        return $this->hasMany(SystemAuthItem::className(), ['rule_name' => 'name']);
    }

    /**
     * 初始化模型 使用编辑时候调用
     * @param $key
     * @return SystemAuthRule|static
     */
    public function initActiveRecord($key)
    {
        $model = new self();
        if (!empty($key)) {
            $item = $model::findOne($key);
            if (!empty($item)) {
                return $item;
            }
        }

        $model->loadDefaultValues();
        return $model;
    }
}

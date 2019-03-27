<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/13 20:04
 */

namespace backend\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class SystemAuthItem
 * @package backend\models
 * @property string $name [varchar(64)]
 * @property int $type [smallint(6)]
 * @property string $description
 * @property string $rule_name [varchar(64)]
 * @property string $data [blob]
 * @property int $created_at [int(11)]
 * @property int $updated_at [int(11)]
 * @property int $sort [int(11) unsigned]
 * @property int $id [int(11) unsigned]
 * @property int $pid [int(11) unsigned]
 * @property int $level [int(11)]
 */
class SystemAuthItem extends ActiveRecord
{

    // 角色类型
    const ROLE = 1;
    // 权限类型
    const AUTH = 2;

    public function rules()
    {
        return [
            //验证不能为空
            [['pid', 'description', 'name', 'type', 'level'], 'required'],
            ['name', 'unique', 'message' => '路由已存在,请重新输入'],
            [['description'], 'string'],
            [['pid'], 'filter', 'filter' => function ($value) {
                $value = intval($value);
                if ($value != 0) {
                    $parent = self::find()->where('id=:id', [':id' => $value])->one();
                    return $parent ? $value : 0;
                }
                return 0;
            }]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pid' => '上级菜单',
            'name' => '路由',
            'type' => '类型',
            'sort' => '排序',
            'level' => '等级',
            'description' => '描述',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 自动插入
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->isNewRecord) {
            //设置key
            $model = self::find()->orderBy('id desc')->select('id')->one();
            $id = $model['id'];
            $this->id = $id ? $id + 1 : 1;
        }

        return true;
    }

    /**
     * @inheritdoc
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
     * 删除子数据
     * @return bool
     */
    public function afterDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        self::deleteAll(['pid' => $this->id]);

        return true;
    }

    /**
     * 获取角色名称
     * @param int $role
     * @return mixed
     */
    public static function roleStatus($role)
    {
        return self::find()->where("id = '{$role}'")->one()['name'];
    }

    public static function roleId($name)
    {
        return self::find()->where(['name' => $name, 'type' => self::ROLE])->one()['id'];
    }

}

<?php

namespace backend\models;


/**
 * This is the model class for table "th_system_auth_item_child".
 *
 * @property string $parent
 * @property string $child
 *
 * @property SystemAuthItem $parent0
 * @property SystemAuthItem $child0
 */
class SystemAuthItemChild extends \yii\db\ActiveRecord
{


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent', 'child'], 'required'],
            [['parent', 'child'], 'string', 'max' => 64],
            [['parent'], 'exist', 'skipOnError' => true, 'targetClass' => SystemAuthItem::className(), 'targetAttribute' => ['parent' => 'name']],
            [['child'], 'exist', 'skipOnError' => true, 'targetClass' => SystemAuthItem::className(), 'targetAttribute' => ['child' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'parent' => 'Parent',
            'child' => 'Child',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent0()
    {
        return $this->hasOne(SystemAuthItem::className(), ['name' => 'parent']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChild0()
    {
        return $this->hasOne(SystemAuthItem::className(), ['name' => 'child']);
    }

//    /**
//     * 重新写入授权
//     * @param $parent  -角色名称
//     * @param $auth    -所有权限
//     * @return bool
//     */
//    public function accredit($parent,$auth){
//        //删除原先所有权限
//        $this::deleteAll(['parent' => $parent]);
//
//        $result= \Yii::$app->db->createCommand()->batchInsert(self::tableName(),['parent','child'],$auth)->execute();
//        return $result?true:false;
//    }
    /**
     * 重新写入授权
     * @param $parent -角色名称
     * @param $data -所有权限
     * @return bool
     */
    public function accredit($parent, $data)
    {
        //删除原先所有权限
        $this::deleteAll(['parent' => $parent]);
        $count = 0;
        foreach ($data as $k => $value) {
            $item['name'] = $parent;
            $item['description'] = SystemAuthItem::find()->where("id = $value")->one()->name;
            $result = self::createEmpowerment($item);
            if (!empty($result)) {
                $count += 1;
            }
        }
        if ($count == count($data)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 角色分配权限
     * @param $item
     * @return bool
     */
    public static function createEmpowerment($item)
    {
        $auth = \Yii::$app->authManager;
        $parent = $auth->createRole($item['name']);
        $child = $auth->createPermission($item['description']);
        $auth->addChild($parent, $child);
        return true;
    }


    /**
     * 获取用户权限
     * @param $id
     * @return array|bool
     */
    public static function getRole($id)
    {
        $model = SystemUser::findIdentity($id);
        $role_id = !empty($model) ? $model->role_id : false;
        $role = SystemAuthItem::roleStatus($role_id) ?: false;
        $child = self::find()->select('child')->where(['parent' => $role])->asArray()->all();
        $result = [];
        if (!empty($child) && is_array($child)) {
            foreach ($child as $item) {
                $result[$item['child']] = $item['child'];
            }
            return $result;
        } else {
            return false;
        }
    }
}

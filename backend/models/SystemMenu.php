<?php

namespace backend\models;

use common\helpers\Utils;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * SystemMenu model
 * @property integer id
 * @property integer pid
 * @property string title
 * @property string link
 * @property string icon
 * @property string group
 * @property string remark
 * @property string sort
 * @property string isShow
 */
class SystemMenu extends ActiveRecord
{
    const CACHE_KEY = '_SystemMenu';

    public function rules()
    {
        return [
            //验证不能为空
            [['icon', 'remark'], 'safe'],
            [['title', 'group'], 'required'],
            [['sort', 'isShow'], 'integer', 'message' => '必须为整数'],
            [['link'], 'match', 'pattern' => '/^\#$|^.*$/', 'message' => '链接不合要求'],
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
            'title' => '标题',
            'link' => '链接',
            'group' => '分组标题',
            'icon' => '图标',
            'sort' => '排序',
            'isShow' => '是否显示',
            'remark' => '备注',
        ];
    }

    public static function getMenus()
    {
        $menus = self::handleMenus();
        Yii::$app->cache->set(self::CACHE_KEY, $menus, '3600');
        return $menus;
    }

    private static function handleMenus()
    {
        // 顶级菜单
        $menus = self::menuRole();
        if (empty($menus) || !is_array($menus)) {
            throw new Exception('菜单初始化失败');
        }
        foreach ($menus as &$menu) {
            if (!empty($menu['url'])) {
                $menu['url'] = Utils::parseUrl($menu['url']);
            }
        }
        $menus = Utils::tree($menus, 0, 'id', 'pid', 'items');

        // 插入标题
        $tmp = [];
        foreach ($menus as $val) {
            $tmp[$val['group']][] = $val;
        }
        $data = [];
        foreach ($tmp as $group => $v) {
            $data[] = ['label' => $group, 'options' => ['class' => 'header']];
            foreach ($v as $i) {
                $data[] = $i;
            }
        }
        unset($menus);
        return $data;
    }

    /**
     * 保存前更新缓存
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        //清除缓存
        Yii::$app->cache->delete(self::CACHE_KEY);

        return true;
    }

    /**
     * 删除前更新缓存
     * @return bool
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        //清除缓存
        Yii::$app->cache->delete(self::CACHE_KEY);

        return true;
    }

    /**
     * 获取用户菜单权限
     * @return array|SystemMenu[]|string|ActiveRecord[]
     */
    public static function menuRole()
    {
        $id = Yii::$app->user->id;
        $role_user = SystemUser::findIdentity($id);
        //判读是否为超级管理员
        if (SystemUser::isSystemAdmin($role_user->username)) {
            $menu = SystemMenu::find()->select(['id', 'title AS label', 'pid', 'icon', 'link AS url', 'group'])->where(['isShow' => 1])->indexBy('id')->orderBy('sort ASC, id ASC')->asArray()->all();
            return $menu;
        }
        if (is_object($role_user) && !empty($role_user)) {
            $role = $role_user['role_id'];
            $role_name = SystemAuthItem::roleStatus($role);
            $menu = SystemAuthItemChild::find()->where("parent = '{$role_name}'")->asArray()->all();
            if (is_array($menu) && !empty($menu)) {
                $menus = [];
                foreach ($menu as $itemChild) {
                    if (substr($itemChild['child'], 0, 1) != '/') {
                        $menus[] = '/' . $itemChild['child'];
                    }
                    $menus[] = $itemChild['child'];
                }
                $menu = SystemMenu::find()->select(['id', 'title AS label', 'pid', 'icon', 'link AS url', 'group'])->where(['in', 'link', $menus])->andWhere(['isShow' => 1])->indexBy('id')->orderBy('sort DESC, id ASC')->asArray()->all();
            } else {
                $menu = false;
            }
        } else {
            $menu = false;
        }
        //Yii::$app->cache->delete(self::CACHE_KEY);

        return $menu;
    }

}

<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/21
 * Time: 10:40
 */

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * @property int $id [int(10) unsigned]  配置ID
 * @property string $name [varchar(50)]  配置标识键
 * @property bool $type [tinyint(3) unsigned]  配置类型
 * @property string $title [varchar(50)]  配置标题
 * @property string $value 配置值
 * @property bool $group [tinyint(3) unsigned]  配置分组
 * @property string $extra [varchar(255)]  配置项
 * @property string $remark [varchar(255)]  配置说明
 * @property bool $status [tinyint(4)]  状态，1显示，0隐藏
 * @property int $sort [smallint(3) unsigned]  排序
 * @property int $updated_at [int(11) unsigned]  修改时间
 * @property int $created_at [int(11) unsigned]  创建时间
 */
class Config extends ActiveRecord
{
    const CACHE_KEY = '_SystemConfig';

    public function rules()
    {
        return [
            //验证不能为空
            [['name', 'title', 'group', 'type', 'value'], 'required'],
            [['name'], 'match', 'pattern' => '/^[A-Za-z1-10_]*$/', 'message' => '配置标识不合要求'],
            ['name', 'unique', 'message' => '标识已经占用'],
            [['sort', 'status'], 'integer', 'message' => '必须为整数'],
            [['extra'], 'string', 'max' => 255],
            [['remark'], 'string', 'max' => 1000],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '配置标识',
            'title' => '标题',
            'type' => '配置类型',
            'group' => '配置分组',
            'value' => '配置值',
            'sort' => '排序',
            'extra' => '配置项',
            'status' => '是否显示',
            'remark' => '说明',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * 设置配置
     * @param $data
     * @throws Exception
     */
    public static function set($data)
    {
        $model = self::findOne(['name' => $data['name']]);
        if (empty($model)) {
            throw new Exception('配置: [' . $data['name'] . '] 不存在');
        }
        if (!@in_array($model->type, [1, 2])) {
            throw new Exception('仅支持修改文本或文本域类型的配置');
        }
        $model->value = $data['value'];
        $saveFields = null;
        if (!$model->save(true, $saveFields)) {
            throw new Exception('配置保存失败' . current($model->getFirstErrors()));
        }
    }

    /**
     * 获取配置值
     * @param $name
     * @return string|array
     */
    public static function getConfig($name)
    {
        $config = self::getConfigAll();
        return isset($config[$name]) ? $config[$name] : '';
    }


    /**
     * 读取配置文件 缓存
     * @return array
     */
    public static function getConfigAll()
    {
        $config = Yii::$app->cache->get(self::CACHE_KEY);
        $config = false;
        if (!$config || !is_array($config)) {
            $list = Config::find()->all();
            $config = [];
            foreach ($list as $row) {
                if ($row['type'] == '7') {
                    $row['value'] = self::parseConfigAttr($row['value']);
                }
                $config[$row['name']] = $row['value'];
            }
            Yii::$app->cache->set(self::CACHE_KEY, $config);
        }
        return $config;
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
     * 分析枚举类型配置值 格式 a:名称1,b:名称2
     * @param $string
     * @return array
     */
    public static function parseConfigAttr($string)
    {
        $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
        if (strpos($string, ':')) {
            $value = [];
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k] = $v;
            }
        } else {
            $value = $array;
        }
        return $value;
    }

}

<?php

namespace backend\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "th_game_config".
 *
 * @property string $name
 * @property string $value
 * @property string $updated_at
 * @property string $created_at
 */
class DistributionConfig extends ActiveRecord
{

    const CACHE_KEY = '_DistributionConfig';

    /**
     * @param $name
     * @return DistributionConfig|null|static
     * @throws Exception
     */
    public static function findByName($name)
    {
        $config = self::findOne($name);
        if (empty($config)) {
            $config = new self();
            $config->name = $name;
            if (!$config->save()) {
                throw new Exception('配置项创建失败！');
            }
        }
        return $config;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['value'], 'string'],
            [['updated_at', 'created_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key' => '配置键',
            'name' => '配置名',
            'value' => '配置值',
            'desc' => '备注',
            'updated_at' => '更新时间',
            'created_at' => '创建时间',
        ];
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
     * 读取配置文件 缓存
     * @param string $name
     * @return array|string
     * @throws Exception
     */
    public static function getAll($name = '')
    {
        $data = \Yii::$app->cache->get(self::CACHE_KEY);
        if (empty($data) || !is_array($data)) {
            $data = [];
            $config = self::find()->indexBy('name')->asArray()->all();
            foreach ($config as $item) {
                $data[$item['name']] = Json::decode($item['value'], true);
            }
            unset($config);
            \Yii::$app->cache->set(self::CACHE_KEY, $data);
        }
        if (!empty($name)) {
            $value = ArrayHelper::getValue($data, $name);
            if ($value === null) {
                throw new Exception('游戏配置不存在，请联系管理员！');
            }
            return $value;
        }
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
        \Yii::$app->cache->delete(self::CACHE_KEY);

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
        \Yii::$app->cache->delete(self::CACHE_KEY);

        return true;
    }
}


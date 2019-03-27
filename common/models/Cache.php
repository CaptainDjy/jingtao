<?php

namespace common\models;

use yii\base\Model;
use yii\db\Exception;

/**
 * Class Cache
 * @package common\models
 */
class Cache extends Model
{
    const KEYS_LABEL = [
        'all' => '全部缓存',
        '_SystemMenu' => '系统菜单缓存',
        '_SystemConfig' => '系统配置缓存',
    ];

    public $cache_systemMenu = '_SystemMenu';
    public $cache_systemConfig = '_SystemConfig';

    /**
     * @param array|string $keys
     * @throws Exception
     */
    public static function clear($keys)
    {
        $cache = \Yii::$app->cache;
        if ($keys == 'all' || (@is_array($keys) && count($keys) && $keys[0] == 'all')) {
            if (!$cache->flush()) {
                throw new Exception('缓存清理失败,请重试');
            }
        } elseif (@is_array($keys) && count($keys)) {
            $fail = ' ';
            foreach ($keys as $key) {
                if (!$cache->delete($key)) {
                    $fail .= Cache::KEYS_LABEL[$key] . ' 、';
                }
            }

            if (!empty(trim($fail))) {
                $fail = mb_substr($fail, 0, mb_strlen($fail) - 1);
                throw new Exception('缓存: ' . $fail . ' 清理失败,请重试');
            }
        }
    }
}

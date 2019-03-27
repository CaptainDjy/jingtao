<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/28 20:58
 */

namespace console\controllers;

use api\models\User;
use common\models\Cooperationuser;
use console\components\ComputerCommission;
use console\components\SyncGoods;
use console\components\SyncModifyOrder;
use console\components\SyncOrder;
use yii\base\Exception;
use yii\console\Controller;

class CrontabController extends Controller
{
    /**
     * 定时任务入口，每一分钟执行一次，如需时间判断，请自行设置redis缓存控制，添加任务请手工添加方法然后在index里面调用
     *
     */
    public function actionIndex()
    {
        for ($i = 0;$i>-1;$i++){
            sleep(3);
            $this->actionHzs();
        }

//        // 定时同步订单
//        $this->syncOrder();
//
//        // 定时修改订单
//        $this->syncModifyOrder();
//
//        // 定时处理分佣
//        $this->computerCommission();
//
//        // 定时同步清理商品
////        $this->syncGoods();
//
//        return;
    }

//合作商过期定时任务
    public function actionHzs()
    {
        //定时查询合作商的信息,如果结束时间小于当前时间,合作商过期
        $cooper = Cooperationuser::find()->where(['status' => 1])->asArray()->all();
        foreach ($cooper as $key => $data) {
            if ($data['end_time'] < time()) {
                $gq = Cooperationuser::find()->where(['status' => 1])->one();
                $gq->status = 0;
                $gq->save();
                $usergq=User::find()->where(['uid'=>$gq->uid])->one();//用户表合作商字段过期
                $usergq->cooperation=0;
                $usergq->save();
            }
        }
    }

    /**
     * 定时同步订单
     */
    private function syncOrder()
    {
        $lockKey = '_CRONTAB_SYNC_ORDER_LOCK';
        $cache = \Yii::$app->cache;
        $lock = $cache->get($lockKey);
        if ($lock) {
            return self::say('INFO', '订单同步时间未到');
        }
        $cache->set($lockKey, TIMESTAMP, 60);

        try {
            $syncOrder = new SyncOrder();
            $syncOrder->run();
            return self::say('INFO', '订单同步已完成');
        } catch (Exception $e) {
            return self::say('ERROR', '订单同步发生错误：' . $e->getMessage());
        }
    }

    /**
     * 定时修改订单
     */
    private function syncModifyOrder()
    {
        $lockKey = '_CRONTAB_SYNC_MODIFY_ORDER_LOCK';
        $cache = \Yii::$app->cache;
        $lock = $cache->get($lockKey);
        if ($lock) {
            return self::say('INFO', '订单同步时间未到');
        }
        $cache->set($lockKey, TIMESTAMP, 60);

        try {
            $syncOrder = new SyncModifyOrder();
            $syncOrder->run();
            return self::say('INFO', '订单同步已完成');
        } catch (Exception $e) {
            return self::say('ERROR', '订单同步发生错误：' . $e->getMessage());
        }
    }

    /**
     * 定时处理分佣
     */
    private function computerCommission()
    {
        $lockKey = '_CRONTAB_COMPUTER_COMMISSION_LOCK';
        $cache = \Yii::$app->cache;
        $lock = $cache->get($lockKey);
        if ($lock) {
            return self::say('INFO', '订单分佣时间未到');
        }
        $cache->set($lockKey, TIMESTAMP, 60);

        try {
            $syncOrder = new ComputerCommission();
            $syncOrder->run();
            return self::say('INFO', '订单分佣已完成');
        } catch (Exception $e) {
            return self::say('ERROR', '订单分佣发生错误：' . $e->getMessage());
        }
    }

    /**
     * 定时同步清理商品
     */
    private function syncGoods()
    {
        $lockKey = '_CRONTAB_SYNC_GOODS_LOCK';
        $cache = \Yii::$app->cache;
        $lock = $cache->get($lockKey);
        if ($lock) {
            return self::say('INFO', '商品同步时间未到');
        }
        $cache->set($lockKey, TIMESTAMP, 60 * 60 * 4);

        try {
            $syncOrder = new SyncGoods();
            $syncOrder->run();
            return self::say('INFO', '商品同步已完成');
        } catch (Exception $e) {
            return self::say('ERROR', '商品同步发生错误：' . $e->getMessage());
        }
    }

    /**
     * 日志记录
     * @param string $category
     * @param $msg
     * @param array $data
     */
    public static function say($category = 'INFO', $msg, $data = [])
    {
        $log = date('Y-m-d H:i:s') . " [{$category}] " . $msg . PHP_EOL;
        if (!empty($data)) {
            $log .= 'DATA:' . PHP_EOL;
            $log .= var_export($data, true) . PHP_EOL;
        }
        echo $log;
    }
}

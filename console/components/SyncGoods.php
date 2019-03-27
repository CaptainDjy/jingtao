<?php
/**
 * @author pine
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * @link http://www.hnbangyao.com
 */

namespace console\components;

use common\components\robots\DataokeRobot;
use common\components\robots\DdkRobot;
use common\components\robots\JdRobot;
use common\models\Goods;
use yii\base\BaseObject;
use yii\db\Exception;
use yii\httpclient\Client;

/**
 * 商品同步和过期清理
 * Class SyncGoods
 * @package console\components
 */
class SyncGoods extends BaseObject
{
    public function run()
    {
        $this->DelGoods();
        $this->RobotTb();
        $this->RobotPdd();
        $this->RobotJd();
    }

    /**
     * 删除过期商品
     */
    public function DelGoods()
    {
        $list = Goods::find()->select('id')->where(['coupon_remained' => 0])->orWhere('end_time <= ' . time())->orWhere('coupon_end_at <= ' . time())->column();
        if (!empty($list)) {
            if (!Goods::deleteAll(['id' => $list])) {
                throw new Exception('商品清理删除失败');
            }
        }
    }

    /**
     * 京东同步商品
     */
    public function RobotJd()
    {
        $robots = new JdRobot();
        while ($robots->pageNum < 200) {
            $robots->pageNum = $robots->pageNum + 1;
            $robots->run();
        }
    }

    /**
     * 京东同步商品
     */
    public function RobotJds()
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setUrl("http://119.29.94.164/xiaocao/jd/search/coupon_item_list.acton?qq=372638426&appkey=37263842620180529&page=1")
            ->setMethod('get')
            ->send();
        // TODO 未完成
    }

    /**
     * 拼多多
     */
    public function RobotPdd()
    {
        $robots = new DdkRobot();
        while ($robots->pageNum < 100) {
            $robots->pageNum = $robots->pageNum + 1;
            $robots->run('total', ['opt_id' => 1]);
        }
    }

    /**
     * 淘宝
     */
    public function RobotTb()
    {
        $robots = new DataokeRobot();
        while ($robots->pageNum < 100) {
            $robots->pageNum = $robots->pageNum + 1;
            $robots->run();
        }
    }

}

<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\components\robots;

use yii\base\BaseObject;

/**
 * Class Robots
 * 采集基础类
 * 1.实现单条采集
 * 2.实现多条采集
 * 3.可选择直接入库或返回数据
 * @package common\components\robots
 */
class Robot extends BaseObject
{
    /**
     * 当前页码
     * @var int
     */
    public $pageNum = 0;

    /**
     * 采集成功数量
     * @var int
     */
    public $num = 0;

    /**
     * 每页多少条
     * @var int
     */
    public $pageSize;

    public function init()
    {
        parent::init();
    }

}

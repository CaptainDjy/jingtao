<?php
/**
 * @author pine
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * createtime: 2018/05/26 17:00
 */


namespace common\models;

use common\components\jd\JdPid;
use common\components\pdd\PddClient;
use common\components\pdd\requests\DdkGoodsPidGenerate;
use common\components\taobao\TaobaoPid;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "dh_advert_space".
 *
 * @property string $id ID
 * @property int $type 类型
 * @property string $pid 广告位ID
 * @property string $title 名称
 * @property string $uid 用户UID
 * @property int $status 状态 0未启用 1已启用
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class AdvertSpace extends ActiveRecord
{
    const TYPE = [
        '1' => '淘宝',
        '2' => '京东',
        '3' => '拼多多',
    ];
    const TYPE_TB = 1;
    const TYPE_JD = 2;
    const TYPE_PDD = 3;

    /**
     * 创建拼多多PID
     * @param $total
     * @param $cur
     * @param string $nameList
     * @return mixed
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public static function createPdd($total, $cur, $nameList = '')
    {
        if ($total < 10) {
            throw new Exception('创建失败，拼多多要求最低10个起');
        }
        $num = min(100, $total - $cur);
        $client = new PddClient();
        $request = new DdkGoodsPidGenerate();
        $request->number = $num;
        if (!empty($nameArray)) {
            $request->p_id_name_list = $nameList;
        }
        $result = $client->run($request);
        if (empty($result['p_id_generate_response']) || empty($result['p_id_generate_response']['p_id_list'])) {
            throw new Exception('创建失败，接口返回数据为空');
        }

        $data = [];
        foreach ($result['p_id_generate_response']['p_id_list'] as $item) {
            array_push($data, [
                3,
                $item['p_id'],
//                $item['p_id_name'],
               '京淘多返利',
                TIMESTAMP,
                TIMESTAMP,
            ]);
        }

        $result = \Yii::$app->db->createCommand()->batchInsert(AdvertSpace::tableName(), ['type', 'pid', 'title', 'created_at', 'updated_at'], $data)->execute();
        if ($result != count($data)) {
            throw new Exception('创建失败, 计划创建 ' . count($data) . '个,实际创建 ' . $result . '个');
        }
        return $num + $cur;
    }

    /**
     * 创建JD推广位
     * @param $total
     * @param $cur
     * @param string $cookies
     * @return mixed
     * @throws \yii\base\Exception
     */
    public static function createJd($total, $cur, $cookies = '')
    {
        $num = min(1, $total - $cur);
        $jd = new JdPid();
        $jd->create($num, $cookies);
        return $num + $cur;
    }

    /**
     * 淘宝广告位创建
     * @param $total
     * @param $cur
     * @param string $siteId
     * @param string $cookie
     * @return mixed
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public static function createTb($total, $cur, $siteId = '', $cookie = '')
    {
        $num = min(5, $total - $cur);
        if (empty($siteId)) {
            throw new Exception('淘宝联盟推广位创建失败，导购ID不能为空');
        }
        if (!empty($cookie)) {
            throw new Exception('淘宝联盟推广位创建失败，淘宝联盟COOKIE不能为空');
        }

        $pid = new TaobaoPid([
            'cookie' => $cookie
        ]);
        for ($i = 0; $i < $num; $i++) {
            $pid->create($siteId);
            usleep(mt_rand(3000000, 5000000));
        }

        return $num + $cur;
    }

    /**
     * @param $data
     * @param $type
     * @return array|mixed
     * @throws Exception
     */
    public static function batchSync($data, $type)
    {
        $model = new self();
        $num = 0;
        $old_num = 0;
        foreach ($data as $key => $item) {
            $old_model = self::find()->where(['pid' => $item['pid'], 'type' => $type])->limit(1)->one();
            if ($old_model) {
                $old_num++;
                continue;
            }
            $_model = clone $model;
            $_model->setAttributes($item);
            if (!$_model->save()) {
                throw new Exception('同步推广位失败:' . current($model->getFirstErrors()));
            }
            $num++;
        }
        return ['new' => $num, 'old' => $old_num];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['update'] = ['pid', 'uid'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'pid'], 'required'],
            [['type', 'uid', 'status', 'created_at', 'updated_at'], 'integer'],
            [['pid', 'title'], 'string', 'max' => 255],
            [['type', 'pid'], 'unique', 'targetAttribute' => ['type', 'pid']],
            [['pid', 'uid'], 'unique', 'targetAttribute' => ['pid', 'uid'], 'on' => 'update'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型',
            'pid' => '广告位ID',
            'title' => '备注',
            'uid' => '用户UID',
            'status' => '状态',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
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
}

<?php

namespace common\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "{{%collection}}".
 *
 * @property int $id 序号
 * @property int $uid 用户UID
 * @property string $thumb 商品图
 * @property string $origin_price 原价
 * @property string $coupon_price 券后价
 * @property string $title 标题
 * @property string $coupon_link 链接
 * @property string $coupon_money 优惠券金额
 * @property string $commission_money 佣金金额
 * @property string $collection_id 商品/店铺ID
 * @property int $type 1商品2店铺
 * @property int $good_type 1 淘宝 2 京东 3 拼多多
 * @property int $status 1收藏2取消
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class Footprint extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%footprint}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uid', 'collection_id'], 'required'],
            [['uid', 'type', 'created_at', 'updated_at', 'status', 'good_type'], 'integer'],
            [['collection_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '序号',
            'uid' => '用户UID',
            'thumb'=>'商品图',
            'title'=>'标题',
            'coupon_money'=>'优惠券金额',
            'origin_price'=>'原价',
            'coupon_price'=>'券后价',
            'commission_money'=>'佣金',
            'coupon_link'=>'链接',
            'collection_id' => '收藏ID',
            'type' => '类型',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * 添加记录
     * @param $data
     * @throws Exception
     */
    public static function add($data)
    {
        $model = new self();
        $model->uid = $data['uid'];
        $model->collection_id = $data['origin_id'];
        $model->thumb=$data['thumb'];
//        $model->title=$data['title'];
//        $model->coupon_money=$data['coupon_money'];
//        $model->origin_price=$data['origin_price'];
        $model->coupon_price=$data['coupon_price'];
//        $model->commission_money=$data['commission_money'];
//        $model->coupon_link=$data['coupon_link'];
        if (!$model->save()) {
            throw new Exception(current($model->getFirstErrors()));
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoods()
    {
        return $this->hasOne(Goods::class, ['origin_id' => 'collection_id'])->select('origin_id,title,thumb,origin_price,coupon_price,coupon_money,');
    }
}

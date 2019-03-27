<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/18
 * Time: 16:02
 */

namespace common\components;


use backend\models\DistributionConfig;
use common\helpers\Utils;
use common\models\Deposit;
use common\models\Message;
use common\models\Order;
use common\models\Recharge;
use common\models\User;
use yii\base\BaseObject;
use yii\db\Expression;

class Distribution extends BaseObject
{
    public $uid, $sumPrice, $order_id, $goods_id, $type;
    private $rela, $price, $money, $userInfo, $orderInfo; //price 返回总金额

    /**
     * @throws \yii\base\Exception
     */
    public function init()
    {
        $this->userInfo = User::find()->where(['uid' => $this->uid])->asArray()->one();
        $relation = rtrim($this->userInfo['superior'], '_0');
        $this->rela = explode('_', $relation);
        $this->price = $this->sumPrice * (1 - DistributionConfig::getAll("index")['platform'] * 0.01);
        $this->money = $this->sumPrice * (1 - DistributionConfig::getAll("index")['platform'] * 0.01 - DistributionConfig::getAll("partner")['deduction'] * 0.01);
        $this->orderInfo = Order::findOne(['trade_id' => $this->order_id, 'product_id' => $this->goods_id]);
    }

    /**
     * 三级分团队奖
     * @param $uid
     * @return bool|int
     * @throws \yii\base\Exception
     */
    public function team($uid)
    {
        $user = User::find()->where(['uid' => $uid])->asArray()->one();
        $relation = rtrim($user['superior'], '_0');
        $rela = explode('_', $relation);
        $num = count($rela);
        if ($num >= 3) {
            $num = 3;
        } else {
            $num = count($rela);
        }
        $re = false;
        if (!empty($rela[0])) {
            $data = $messageData = [];
            for ($i = 0; $i < $num; $i++) {
                $relaInfo = User::find()->where(['uid' => $rela[$i]])->one();
                if (!empty($relaInfo)) {
                    if ($relaInfo->lv == 3) {
                        $ratio = DistributionConfig::getAll("index")['team'][$i + 1] * 0.01;
                        $money = $this->price * $ratio;
                        $data[$i] = [
                            'uid' => $rela[$i],
                            'type' => 2,
                            'order_id' => $this->order_id,
                            'goods_id' => $this->goods_id,
                            'order_type' => $this->type,
                            'price' => Utils::getTwoPrice($money, 2),
                            'credit' => 3,
                            'status' => $this->orderInfo['order_status'],
                            'created_at' => $this->orderInfo['created_at'],
                            'updated_at' => time(),
                        ];
                        $messageData [] = [
                            'uid' => $rela[$i],
                            'text' => '您的团队奖励增加' . $money . '元',
                            'created_at' => time(),
                            'updated_at' => time(),
                        ];
                        $re = $relaInfo->updateAttributes([
                            'credit3' => new Expression("credit3+" . $money),
                        ]);
                    }
                }
            }
            Recharge::addOrder($data);
            Message::addOrder($messageData);
        }
        return $re;
    }

    /**
     * 判断用户是否升级
     * @return bool
     * @throws \yii\base\Exception
     */
    public function upgrade()
    {
        $directPayment = User::find()->where("superior REGEXP '^{$this->uid}_'")->andWhere("lv !=0")->asArray()->count(1); //直推付费
        $directNum = User::find()->where("superior REGEXP '^{$this->uid}_'")->asArray()->count(1); //直推总人数
        $teamPayment = User::find()->where("superior REGEXP '^{$this->uid}_|_{$this->uid}_' ")->andWhere("lv !=0")->asArray()->count(1); //团队付费
        $teamNum = User::find()->where("superior REGEXP '^{$this->uid}_|_{$this->uid}_' ")->asArray()->count(1); //团队总人数
        if ($this->userInfo['lv'] == 0) {
            $num = DistributionConfig::getAll("index")['rechargeback'][1]['directNum'];
            if ($directNum >= $num) {
                $user = User::findOne(['uid' => $this->uid]);
                if (!empty($user)) {
                    if ($user->lv < 3) {
                        $user->updateAttributes(['lv' => new Expression('lv+1')]);
                    }
                    return true;
                }
            } else {
                return false;
            }
        } else {
            if ($this->userInfo['lv'] <= 2) {
                $num1 = DistributionConfig::getAll("index")['rechargeback'][$this->userInfo['lv'] + 1]['directPayment'];
                $num2 = DistributionConfig::getAll("index")['rechargeback'][$this->userInfo['lv'] + 1]['directNum'];
                $num3 = DistributionConfig::getAll("index")['rechargeback'][$this->userInfo['lv'] + 1]['teamPayment'];
                $num4 = DistributionConfig::getAll("index")['rechargeback'][$this->userInfo['lv'] + 1]['teamNum'];
                if ($directPayment >= $num1 && $directNum >= $num2 && $teamPayment >= $num3 && $teamNum >= $num4) {
                    $user = User::findOne(['uid' => $this->uid]);
                    if (!empty($user)) {
                        if ($user->lv < 3) {
                            $user->updateAttributes(['lv' => new Expression('lv+1')]);
                        }
                        return true;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * 分钱
     * @return bool|int
     * @throws \yii\base\Exception
     */
    public function disPrice()
    {
        $user = User::findOne(['uid' => $this->uid]);
        if ($this->userInfo['lv'] <= 0) {  //购物者是粉丝或者路人
            $ratio = DistributionConfig::getAll("index")['selfcomm'];
            $relaInfo = User::findOne(['uid' => $this->rela[0]]);
            if (!empty($relaInfo)) {
                $messageData = $data = $datas = [];
                $user->updateAttributes([
                    'credit' => new Expression('credit+' . $this->price * $ratio[0] * 0.01),
                ]);
                $messageData[] = [
                    'uid' => $this->uid,
                    'text' => '您的自购返现增加了' . $this->price * $ratio[0] * 0.01,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
                $datas[] = [
                    'uid' => $this->uid,
                    'type' => 1,
                    'order_id' => $this->order_id,
                    'goods_id' => $this->goods_id,
                    'order_type' => $this->type,
                    'price' => Utils::getTwoPrice($this->price * $ratio[0] * 0.01, 2),
                    'credit' => 2,
                    'status' => $this->orderInfo['order_status'],
                    'created_at' => $this->orderInfo['created_at'],
                    'updated_at' => time(),
                ];
                Recharge::addOrder($datas);
                if ($relaInfo->lv >= 0) { //上级购买过会员
                    $relaPrice = $this->price * ($ratio[$relaInfo->lv] - $ratio[$user->lv]) * 0.01;
                    $relaInfo->updateAttributes([
                        'credit' => new Expression('credit+' . $relaPrice),
                    ]);
                    $messageData[] = [
                        'uid' => $this->uid,
                        'text' => '您的返现增加了' . $relaPrice,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                    $data[] = [
                        'uid' => $this->rela[0],
                        'type' => 3,
                        'order_id' => $this->order_id,
                        'goods_id' => $this->goods_id,
                        'order_type' => $this->type,
                        'price' => Utils::getTwoPrice($relaPrice, 2),
                        'credit' => 2,
                        'status' => $this->orderInfo['order_status'],
                        'created_at' => $this->orderInfo['created_at'],
                        'updated_at' => time(),
                    ];
                    Recharge::addOrder($data);
                    Message::addOrder($messageData);
                    if ($relaInfo->lv == 3) {
                        return $this->team($relaInfo->uid);
                    }
                    $lvMoney = Utils::getTwoPrice($this->price - $relaPrice - $this->price * $ratio[0] * 0.01, 2);
                    $relaPrice1 = $lvMoney * DistributionConfig::getAll("index")['consump'][1] * 0.01;
                    $relaPrice2 = $lvMoney * DistributionConfig::getAll("index")['consump'][2] * 0.01;
                    $relaPrice3 = $lvMoney * DistributionConfig::getAll("index")['consump'][3] * 0.01;

                    if (!empty($this->rela[1])) {  // 二级
                        $relaUser1 = User::findOne(['uid' => $this->rela[1]]);
                        if (!empty($relaUser1)) {
                            if ($relaUser1->lv > 0 && $relaUser1->lv >= 1) {
                                $depositPrice = $lvMoney - $relaPrice1;
                                $this->disRela($this->rela[1], $relaPrice1, Utils::getTwoPrice($depositPrice, 2));
                                if ($relaUser1->lv == 3) {
                                    return true;
                                }
                            }
                        }
                    }
                    if (!empty($this->rela[2])) {
                        $relaUser2 = User::findOne(['uid' => $this->rela[2]]);
                        if (!empty($relaUser2)) {
                            if ($relaUser2->lv > 0 && $relaUser2->lv >= 2) {
                                $depositPrice = Utils::getTwoPrice($lvMoney - $relaPrice1 - $relaPrice2, 2);
                                $this->disRela($this->rela[2], $relaPrice2, $depositPrice);
                                if ($relaUser2->lv == 3) {
                                    return true;
                                }
                            }
                        }
                    }
                    if (!empty($this->rela[3])) {
                        $relaUser3 = User::findOne(['uid' => $this->rela[3]]);
                        if (!empty($relaUser3)) {
                            if ($relaUser3->lv > 0 && $relaUser3->lv >= 3) {
                                $depositPrice = Utils::getTwoPrice($lvMoney - $relaPrice1 - $relaPrice2 - $relaPrice3, 2);
                                $this->disRela($this->rela[3], $relaPrice3, $depositPrice);
                                if ($relaUser3->lv == 3) {
                                    return true;
                                }
                            }
                        }
                    }
                    return true;
                } else { // 上级等级是粉丝 所有上级不返
                    return true;
                }
            } else {
                return true;
            }
        } elseif ($this->userInfo['lv'] > 0 && $this->userInfo['lv'] <= 2) {
            $userPrice = $this->price * DistributionConfig::getAll("index")['selfcomm'][$this->userInfo['lv']] * 0.01;
            $user->updateAttributes([
                'credit' => new Expression('credit+' . $userPrice),
            ]);
            $data[] = [
                'uid' => $this->uid,
                'type' => 1,
                'order_id' => $this->order_id,
                'goods_id' => $this->goods_id,
                'order_type' => $this->type,
                'price' => Utils::getTwoPrice($userPrice, 2),
                'credit' => 2,
                'status' => $this->orderInfo['order_status'],
                'created_at' => $this->orderInfo['created_at'],
                'updated_at' => time(),
            ];
            $messageData[] = [
                'uid' => $this->uid,
                'text' => '您的自购佣金增加了' . $userPrice,
                'created_at' => time(),
                'updated_at' => time(),
            ];
            Recharge::addOrder($data);
            Message::addOrder($messageData);
            $lvMoney = $this->price - $userPrice;
            $relaPrice1 = $lvMoney * DistributionConfig::getAll("index")['consump'][1] * 0.01;
            $relaPrice2 = $lvMoney * DistributionConfig::getAll("index")['consump'][2] * 0.01;
            $relaPrice3 = $lvMoney * DistributionConfig::getAll("index")['consump'][3] * 0.01;
            if (!empty($this->rela[0])) {  // 二级
                $relaUser0 = User::findOne(['uid' => $this->rela[0]]);
                if (!empty($relaUser0)) {
                    if ($relaUser0->lv > 0 && $relaUser0->lv >= 1) {
                        $depositPrice = Utils::getTwoPrice($lvMoney - $relaPrice1, 2);
                        $this->disRela($this->rela[0], $relaPrice1, $depositPrice);
                        if ($relaUser0->lv == 3) {
                            return true;
                        }
                    }
                }
            }
            if (!empty($this->rela[1])) {
                $relaUser1 = User::findOne(['uid' => $this->rela[1]]);
                if (!empty($relaUser1)) {
                    if ($relaUser1->lv > 0 && $relaUser1->lv >= 2) {
                        $depositPrice = Utils::getTwoPrice($lvMoney - $relaPrice1 - $relaPrice2, 2);
                        $this->disRela($this->rela[1], $relaPrice2, $depositPrice);
                        if ($relaUser1->lv == 3) {
                            return true;
                        }
                    }
                }
            }
            if (!empty($this->rela[2])) {
                $relaUser2 = User::findOne(['uid' => $this->rela[2]]);
                if (!empty($relaUser2)) {
                    if ($relaUser2->lv > 0 && $relaUser2->lv >= 3) {
                        $depositPrice = Utils::getTwoPrice($lvMoney - $relaPrice1 - $relaPrice2 - $relaPrice3, 2); // TODO 沉淀金额 保存数据表
                        $this->disRela($this->rela[2], $relaPrice3, $depositPrice);
                        if ($relaUser2->lv == 3) {
                            return true;
                        }
                    }
                }
            }
            return true;
        } else {
            $user->updateAttributes([
                'credit' => new Expression("credit+" . Utils::getTwoPrice($this->price, 2))
            ]);
            $data[] = [
                'uid' => $this->uid,
                'type' => 1,
                'order_id' => $this->order_id,
                'goods_id' => $this->goods_id,
                'order_type' => $this->type,
                'price' => Utils::getTwoPrice($this->price, 2),
                'credit' => 2,
                'status' => $this->orderInfo['order_status'],
                'created_at' => $this->orderInfo['created_at'],
                'updated_at' => time(),
            ];
            $messageData[] = [
                'uid' => $this->uid,
                'text' => '您的自购佣金增加了' . $this->price,
                'created_at' => $this->orderInfo['created_at'],
                'updated_at' => time(),
            ];
            Recharge::addOrder($data);
            Message::addOrder($messageData);
            return $this->team($this->uid);
        }
    }

    /**
     * 上级分钱
     * @param $uid
     * @param $price
     * @param int $depositPrice
     * @return bool|int
     * @throws \yii\base\Exception
     */
    public function disRela($uid, $price, $depositPrice = 0)
    {
        $relaUser = User::findOne(['uid' => $uid]);
        if (!empty($relaUser)) {
            $relaUser->updateAttributes([
                'credit2' => new Expression('credit2+' . Utils::getTwoPrice($price, 2)),
            ]);
            $messageData[] = [
                'uid' => $uid,
                'text' => '您的推荐佣金增加了 ' . Utils::getTwoPrice($price, 2),
                'created_at' => time(),
                'updated_at' => time(),
            ];
            $data[] = [
                'uid' => $uid,
                'type' => 3,
                'order_id' => $this->order_id,
                'goods_id' => $this->goods_id,
                'order_type' => $this->type,
                'price' => Utils::getTwoPrice($price, 2),
                'credit' => 2,
                'status' => $this->orderInfo['order_status'],
                'created_at' => $this->orderInfo['created_at'],
                'updated_at' => time(),
            ];
            Recharge::addOrder($data);
            Message::addOrder($messageData);
            if ($relaUser->lv == 3) {
                Deposit::add($this->uid, Utils::getTwoPrice($depositPrice, 2));
                return $this->team($uid);
            } else {
                return true;
            }
        }
    }

    /**
     * @return bool|int
     * @throws \yii\base\Exception
     */
    public function disMoney()
    {
        $user = User::findOne(['uid' => $this->uid]);
        if ($this->userInfo['lv'] <= 0) {  //购物者是粉丝或者路人
            $ratio = DistributionConfig::getAll("index")['selfcomm'];
            $relaInfo = User::findOne(['uid' => $this->rela[0]]);
            if (!empty($relaInfo)) {
                $user->updateAttributes([
                    'credit4' => new Expression('credit4+' . $this->money * $ratio[0] * 0.01),
                ]);
                if ($relaInfo->lv >= 0) { //上级购买过会员
                    $relaPrice = $this->money * ($ratio[$relaInfo->lv] - $ratio[$user->lv]) * 0.01;
                    $relaInfo->updateAttributes([
                        'credit4' => new Expression('credit4+' . $relaPrice),
                    ]);
                    if ($relaInfo->lv == 3) {
                        return $this->teamMoney($relaInfo->uid);
                    }
                    $lvMoney = Utils::getTwoPrice($this->money - $relaPrice - $this->money * $ratio[0] * 0.01, 2);
                    $relaPrice1 = $lvMoney * DistributionConfig::getAll("index")['consump'][1] * 0.01;
                    $relaPrice2 = $lvMoney * DistributionConfig::getAll("index")['consump'][2] * 0.01;
                    $relaPrice3 = $lvMoney * DistributionConfig::getAll("index")['consump'][3] * 0.01;

                    if (!empty($this->rela[1])) {  // 二级
                        $relaUser1 = User::findOne(['uid' => $this->rela[1]]);
                        if (!empty($relaUser1)) {
                            if ($relaUser1->lv > 0 && $relaUser1->lv >= 1) {
                                $this->disRelaMoney($this->rela[1], $relaPrice1);
                                if ($relaUser1->lv == 3) {
                                    return true;
                                }
                            }
                        }
                    }
                    if (!empty($this->rela[2])) {
                        $relaUser2 = User::findOne(['uid' => $this->rela[2]]);
                        if (!empty($relaUser2)) {
                            if ($relaUser2->lv > 0 && $relaUser2->lv >= 2) {
                                $this->disRelaMoney($this->rela[2], $relaPrice2);
                                if ($relaUser2->lv == 3) {
                                    return true;
                                }
                            }
                        }
                    }
                    if (!empty($this->rela[3])) {
                        $relaUser3 = User::findOne(['uid' => $this->rela[3]]);
                        if (!empty($relaUser3)) {
                            if ($relaUser3->lv > 0 && $relaUser3->lv >= 3) {
                                $this->disRelaMoney($this->rela[3], $relaPrice3);
                                if ($relaUser3->lv == 3) {
                                    return true;
                                }
                            }
                        }
                    }
                    return true;
                } else { // 上级等级是粉丝 所有上级不返
                    return true;
                }
            } else {
                return true;
            }
        } elseif ($this->userInfo['lv'] > 0 && $this->userInfo['lv'] <= 2) {
            $userPrice = $this->money * DistributionConfig::getAll("index")['selfcomm'][$this->userInfo['lv']] * 0.01;
            $user->updateAttributes([
                'credit4' => new Expression('credit4+' . $userPrice),
            ]);
            $lvMoney = $this->money - $userPrice;
            $relaPrice1 = $lvMoney * DistributionConfig::getAll("index")['consump'][1] * 0.01;
            $relaPrice2 = $lvMoney * DistributionConfig::getAll("index")['consump'][2] * 0.01;
            $relaPrice3 = $lvMoney * DistributionConfig::getAll("index")['consump'][3] * 0.01;
            if (!empty($this->rela[0])) {  // 二级
                $relaUser0 = User::findOne(['uid' => $this->rela[0]]);
                if (!empty($relaUser0)) {
                    if ($relaUser0->lv > 0 && $relaUser0->lv >= 1) {
                        $this->disRelaMoney($this->rela[0], $relaPrice1);
                        if ($relaUser0->lv == 3) {
                            return true;
                        }
                    }
                }
            }
            if (!empty($this->rela[1])) {
                $relaUser1 = User::findOne(['uid' => $this->rela[1]]);
                if (!empty($relaUser1)) {
                    if ($relaUser1->lv > 0 && $relaUser1->lv >= 2) {
                        $this->disRelaMoney($this->rela[1], $relaPrice2);
                        if ($relaUser1->lv == 3) {
                            return true;
                        }
                    }
                }
            }
            if (!empty($this->rela[2])) {
                $relaUser2 = User::findOne(['uid' => $this->rela[2]]);
                if (!empty($relaUser2)) {
                    if ($relaUser2->lv > 0 && $relaUser2->lv >= 3) {
                        $this->disRelaMoney($this->rela[2], $relaPrice3);
                        if ($relaUser2->lv == 3) {
                            return true;
                        }
                    }
                }
            }
            return true;
        } else {
            $user->updateAttributes([
                'credit4' => new Expression("credit4+" . Utils::getTwoPrice($this->money, 2))
            ]);
            return $this->teamMoney($this->uid);
        }
    }

    /**
     * @param $uid
     * @param $price
     * @return bool|int
     * @throws \yii\base\Exception
     */
    public function disRelaMoney($uid, $price)
    {
        $relaUser = User::findOne(['uid' => $uid]);
        if (!empty($relaUser)) {
            $relaUser->updateAttributes([
                'credit4' => new Expression('credit4+' . Utils::getTwoPrice($price, 2)),
            ]);
            if ($relaUser->lv == 3) {
                return $this->teamMoney($uid);
            } else {
                return true;
            }
        }
    }

    /**
     * @param $uid
     * @return bool|int
     * @throws \yii\base\Exception
     */
    public function teamMoney($uid)
    {
        $user = User::find()->where(['uid' => $uid])->asArray()->one();
        $relation = rtrim($user['superior'], '_0');
        $rela = explode('_', $relation);
        $num = count($rela);
        if ($num >= 3) {
            $num = 3;
        } else {
            $num = count($rela);
        }
        $re = false;
        if (!empty($rela[0])) {
            for ($i = 0; $i < $num; $i++) {
                $relaInfo = User::find()->where(['uid' => $rela[$i]])->one();
                if (!empty($relaInfo)) {
                    if ($relaInfo->lv == 3) {
                        $ratio = DistributionConfig::getAll("index")['team'][$i + 1] * 0.01;
                        $money = $this->money * $ratio;
                        $re = $relaInfo->updateAttributes([
                            'credit4' => new Expression("credit4+" . $money),
                        ]);
                    }
                }
            }
        }
        return $re;
    }

}
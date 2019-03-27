<?php

namespace common\models;


use common\helpers\Utils;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * @property int $uid [int(11) unsigned]  用户uid
 * @property string $mobile [varchar(11)]  手机号
 * @property string $password_hash [varchar(255)]  密码
 * @property string $auth_key [varchar(50)]  cookie验证
 * @property bool $error_password [tinyint(1) unsigned]  密码错误次数
 * @property string $pay_password [varchar(255)]  支付密码
 * @property int $credit [decimal(11,2) unsigned]  余额
 * @property string $realname [varchar(50)]  真实姓名
 * @property string $nickname [varchar(50)]  昵称
 * @property int $created_at [int(11) unsigned]  创建时间
 * @property int $updated_at [int(11) unsigned]  更新时间
 * @property bool $status [tinyint(1)]  0正常,9冻结,8软删除
 * @property string $superior [varchar(255)]  上级列表
 * @property string $referrer [varchar(11)]  推荐人
 * @property int $lock_version [int(11) unsigned]
 * @property bool $lv [smallint(10)] 会员等级
 * @property string $credit1 [decimal(11,2) unsigned] 志愿者余额
 * @property string $credit2 [decimal(11,2) unsigned] 志愿者余额
 * @property string $credit3 [decimal(11,2) unsigned] 志愿者余额
 * @property string $credit4 [decimal(11,2) unsigned] 志愿者余额
 * @property string $avatar [varchar(255)]  头像
 * @property bool $gender [tinyint(1) unsigned]  性别
 * @property string $access_token [varchar(255)]  api接口密匙
 * @property string $wechat_openid [varchar(255)]  微信openID
 * @property string $wechat_access_token [varchar(100)]  微信access_token
 * @property string $wechat_unionid [varchar(50)]  微信unionid
 * @property string $alimm_pid [varchar(100)]  淘宝联盟广告位
 * @property string $jd_pid [varchar(100)]  京东广告位
 * @property string $pdd_pid [varchar(100)]  拼多多广告位
 * @property string $invite_code [varchar(10)]  邀请码
 * @property string $withdraw_to [varchar(50)]  提现账户
 * @property string $frozen [decimal(10,2) unsigned]  提现冻结金额
 * @property string $identity_card [varchar(20)]  身份证号
 */
class User extends ActiveRecord implements IdentityInterface
{
    const PASSWORD_DEFAULT = 'jtsp888';
    const STATUS_ACTIVE = 0;
    const STATUS_DELETED = 9;
    const INTEGRAL_ADD = 1;
    const INTEGRAL_SUB = 2;


    private $password_reset_token;

    /**
     * @param $openid
     * @return null|static
     */

    public static function findByOpenid($openid)
    {
        $model = self::findOne(['wechat_openid' => $openid]);
        if (empty($model)) {
            return null;
        }
        return $model;
    }

    /**
     * @param $unionid
     * @return null|static
     */
    public static function findByUnionid($unionid)
    {
        $model = self::findOne(['wechat_unionid' => $unionid]);
        if (empty($model)) {
            return null;
        }
        return $model;
    }

    /**
     * @param $userinfo
     * @return User
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public static function registerByInviteCode($userinfo)
    {
        $model = new self();
        $model->loadDefaultValues();
        $model->validateReferrer($userinfo['invite_code']);
        $model->setPassword(self::PASSWORD_DEFAULT);
        $model->generateAuthKey();
        $model->generateAccessToken();
        $model->generatePasswordResetToken();
        $model->wechat_openid = $userinfo['openid'];
        $model->wechat_unionid = $userinfo['unionid'];
        $model->nickname = $userinfo['nickname'];
        $model->gender = $userinfo['sex'];
        $model->avatar = $userinfo['headimgurl'];
        $model->invite_code = Utils::genderRandomStr();
        if (!$model->save()) {
            throw new \yii\db\Exception('创建用户失败:' . current($model->getFirstErrors()));
        }
        return $model;
    }

    /**
     * 校验推荐人
     * @param $referrer
     * @return null|static
     */
    public static function findByReferrer($referrer)
    {
        $model = self::findOne(['referrer' => $referrer]);
        if (empty($model)) {
            return null;
        }
        return $model;
    }

    /**
     * 校验推荐码
     * @param $invite_code
     * @return null|static
     */
    public static function findByInviteCode($invite_code)
    {
        $model = self::findOne(['invite_code' => $invite_code, 'status' => self::STATUS_ACTIVE]);
        if (empty($model)) {
            return null;
        }
        return $model;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['first'] = ['mobile'];
        $scenarios['register'] = [
            'mobile',
            'password_hash',
            'referrer',
            'superior',
        ];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile'], 'match', 'pattern' => '/^1[0-9]{10}$/', 'message' => '手机号必须为1开头的11位纯数字'],
            [['mobile'], 'unique', 'message' => '手机号已经存在', 'on' => ['first', 'register']],
            [['password_hash'], 'required'],
            [['alimm_pid', 'jd_pid', 'pdd_pid'], 'required', 'on' => 'register'],
            [['uid', 'error_password', 'gender', 'status', 'updated_at', 'created_at', 'lock_version',], 'integer'],
            [['credit', 'credit2', 'credit3', 'credit4',], 'number'],
            [['mobile', 'referrer'], 'string', 'max' => 11],
            [['password_hash', 'password_reset_token', 'pay_password', 'avatar', 'access_token', 'wechat_access_token',], 'string', 'max' => 255],
            [['realname',], 'string', 'max' => 15],
            [['superior'], 'string', 'max' => 2000],
            [['wechat_unionid', 'withdraw_to', 'nickname', 'auth_key', 'wechat_openid'], 'string', 'max' => 50],
            [['credit',], 'number', 'min' => 0, 'tooSmall' => '{attribute}数量不足！'],
            [['identity_card'], 'string', 'max' => 20],
            [['frozen',], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => 'UID',
            'mobile' => '手机号',
            'superior' => "上级关系",
            'password_hash' => '密码',
            'auth_key' => 'cookie验证',
            'password_reset_token' => 'Password Reset Token',
            'error_password' => '密码错误次数',
            'credit' => '自购返现金额',
            'credit1' => '推荐费',
            'credit2' => '佣金',
            'credit3' => '团队奖',
            'credit4' => '总金额',
            'nickname' => '昵称',
            'realname' => '真实姓名',
            'status' => '状态',
            'referrer' => '推荐人手机号',
            'lv' => /*消费级别*/'会员等级',
            'wechat_openid' => '微信openid',
            'alimm_pid' => '淘宝广告位',
            'jd_pid' => '京东广告位',
            'pdd_pid' => '拼多多广告位',
            'invite_code' => '邀请码',
            'wechat_access_token' => '微信access_token',
            'wechat_unionid' => '微信unionid',
            'withdraw_to' => '提现账户',
            'frozen' => '提现冻结金额',
            'identity_card' => '身份证号',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'recommend'=>'直推人数',

        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($uid)
    {
        return static::findOne(['uid' => $uid, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * 生成access_token
     * @throws Exception
     */
    public function generateAccessToken()
    {
        //$this->access_token = Yii::$app->security->generateRandomString();
        $this->update(['access_token' => Yii::$app->security->generateRandomString()]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $token = json_decode(base64_decode($token), true);
        if (!empty($token['uid']) && !empty($token['timestamp']) && ($token['timestamp'] + \Yii::$app->user->authTimeout) >= time()) {
            $user = static::findIdentity($token['uid']);
            if (!empty($user) && $token['sign'] === md5($token['uid'] . $token['timestamp'] . $user->access_token)) {
                return $user;
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function findByCardNum($cardNum)
    {
        return static::findOne(['cardNum' => $cardNum, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     * @param $password
     * @throws Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password, 6);
    }

    /**
     * Generates "remember me" authentication key
     * @throws Exception
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     * @throws Exception
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * 获取用户下级
     * @param $uid
     * @param $level
     * @param $curPage
     * @param $numPerPage
     * @return array|ActiveRecord[]
     */
    public static function getSuperior($uid, $level, $curPage, $numPerPage)
    {
        $condition = "grade = $level AND (superior Like '{$uid}-%'  OR superior Like '%-{$uid}-%')";
        $query = static::find()->select('uid,nickname,superior');
        $offset = $numPerPage * ($curPage - 1);
        $user = $query->where($condition)
            ->offset($offset)
            ->limit($numPerPage)
            ->asArray()
            ->all();
        return $user;
    }

    /**
     * Finds user by mobile
     *
     * @param $mobile
     * @return null|static
     */
    public static function findByMobile($mobile)
    {
        return static::findOne(['mobile' => $mobile, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by uid
     *
     * @param $uid
     * @return self
     * @throws \yii\db\Exception
     */
    public static function findByUid($uid)
    {
        $user = static::findOne(['uid' => $uid, 'status' => self::STATUS_ACTIVE]);
        if (empty($user)) {
            throw new \yii\db\Exception('用户不存在或被停用');
        }
        return $user;
    }

    /**
     * @inheritdoc
     */
    public function optimisticLock()
    {
        return 'lock_version';
    }


    /**
     * @param $data
     * @return bool
     */
    public function addUserLoginLog($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        $this->created_at = time();
        return $this->save();
    }

    /**
     * 查找直推个数
     * @param $uid
     * @param $page
     * @return array|ActiveRecord[]
     */
    public static function findChildrenNum($uid, $page)
    {
        $info = User::find()->select('uid,nickname,lv,created_at,avatar,tb_account as mobile,tb_account')->where(" superior REGEXP '^{$uid}_' ")->offset($page * 10)->limit(10)->orderBy("lv desc,uid desc")->asArray()->all();
//        print_r($info);
//        exit;
        if (!empty($info)) {
            foreach ($info as $k => &$v) {
                $v['avatar'] = Utils::toMedia($v['avatar']);
                $v['created_at'] = date('Y-m-d', $v['created_at']);
                $v['num'] = User::find()->where("superior REGEXP '^{$v['uid']}_' or superior REGEXP '^[0-9]+_{$v['uid']}_' or superior REGEXP '^[0-9]+_[0-9]+_{$v['uid']}_' ")->count(1);
                if (!empty($v['mobile']) and strlen($v['mobile']) > 7) {
                    $v['mobile'] = substr_replace($v['mobile'], '****', 3, 4);
                }
            }
        }
        return $info;
    }

    /**
     * 二代个数信息
     * @param $uid
     * @param $page
     * @return array|ActiveRecord[]
     */
    public static function findChildrenNum2($uid, $page)
    {
        $info = User::find()->select('uid,mobile,nickname,lv,created_at,avatar')->where(" superior REGEXP '^[0-9]+_{$uid}_' ")->offset($page * 10)->limit(10)->orderBy("lv desc,uid desc")->asArray()->all();
        if (!empty($info)) {
            foreach ($info as $k => &$v) {
                $v['avatar'] = Utils::toMedia($v['avatar']);
                $v['created_at'] = date('Y-m-d', $v['created_at']);
                $v['num'] = User::find()->where(" superior REGEXP '^{$v['uid']}_' or superior REGEXP '^[0-9]+_{$v['uid']}_' or superior REGEXP '^[0-9]+_[0-9]+_{$v['uid']}_' ")->count(1);
                if (!empty($v['mobile'])) {
                    $v['mobile'] = substr_replace($v['mobile'], '****', 3, 4);
                }
            }
        }
        return $info;
    }

    /**
     * 三代个数信息
     * @param $uid
     * @param $page
     * @return array|ActiveRecord[]
     */
    public static function findChildrenNum3($uid, $page)
    {
        $info = User::find()->select('uid,mobile,nickname,lv,created_at,avatar')->where(" superior REGEXP '^[0-9]+_[0-9]+_{$uid}_' ")->offset($page * 10)->limit(10)->orderBy("lv desc,uid desc")->asArray()->all();
        if (!empty($info)) {
            foreach ($info as $k => &$v) {
                $v['avatar'] = Utils::toMedia($v['avatar']);
                $v['created_at'] = date('Y-m-d', $v['created_at']);
                $v['num'] = User::find()->where("superior REGEXP '^{$v['uid']}_' or superior REGEXP '^[0-9]+_{$v['uid']}_' or superior REGEXP '^[0-9]+_[0-9]+_{$v['uid']}_' ")->count(1);// superior REGEXP '^{$v['uid']}_'
                if (!empty($v['mobile'])) {
                    $v['mobile'] = substr_replace($v['mobile'], '****', 3, 4);
                }
            }
        }
        return $info;
    }

    /**
     * 所有个数信息
     * @param $uid
     * @param $page
     * @return array|ActiveRecord[]
     */
    public static function findChildrenNumAll($uid, $page)
    {
//        $info = User::find()->select('uid,mobile,nickname,lv,created_at,avatar')->where(" superior REGEXP '^{$uid}_|_{$uid}_' ")->offset($page * 10)->limit(10)->orderBy("lv desc,uid desc")->asArray()->all();
        $info = User::find()->select('uid,nickname,lv,created_at,avatar,tb_account as mobile')->where(" superior REGEXP '^{$uid}_' or superior REGEXP '^[0-9]+_{$uid}_' or superior REGEXP '^[0-9]+_[0-9]+_{$uid}_' ")->offset($page * 10)->limit(10)->orderBy("lv desc,uid desc")->asArray()->all();
        if (!empty($info)) {
            foreach ($info as $k => &$v) {
                $v['avatar'] = Utils::toMedia($v['avatar']);
                $v['created_at'] = date('Y-m-d', $v['created_at']);
                $v['num'] = User::find()->where(" superior REGEXP '^{$v['uid']}_' or superior REGEXP '^[0-9]+_{$v['uid']}_' or superior REGEXP '^[0-9]+_[0-9]+_{$v['uid']}_' ")->count(1);
                if (!empty($v['mobile']) and strlen($v['mobile']) > 7) {
                    $v['mobile'] = substr_replace($v['mobile'], '****', 3, 4);
                }
            }
        }
        return $info;
    }

    public static function findNameByid($uid)
    {
        $name = User::findOne(['uid' => $uid]);
        return $name;
    }

    /**
     * @param int $parentId
     * @return array
     */
    public static function getRegion($parentId = 0)
    {
        $result = Region::find()->where(['parent_id' => $parentId])->asArray()->all();
        return ArrayHelper::map($result, 'id', 'name');
    }

    /**
     * 我的直推信息
     * @param $uid
     * @param $page
     * @return mixed
     */
    public static function ChildrenList($uid, $page)
    {
        $count = User::find()->where(" superior REGEXP '^{$uid}_' ")->count(1);
        $list = User::find()->where(" superior REGEXP '^{$uid}_' ")->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        $data = [];
        foreach ($list as $K => $item) {
            $data[$K]['uid'] = 'HC' . substr($item['mobile'], -6);
            $data[$K]['realname'] = $item['realname'];
            $data[$K]['mobile'] = $item['mobile'];
            $data[$K]['outs'] = $item['outs'];
            $data[$K]['created_at'] = date("Y-m-d H:i:s", $item['created_at']);
        }
        $ls['page'] = ceil($count / 10);
        $ls['list'] = $data;
        return $ls;
    }

    /**
     * 查找下级
     * @param $data
     * @return array
     */
    public static function disTree($data)
    {
        $tree = array();
        foreach ($data as $item) {
            $pid = 0;
            if ($item['superior'] !== "0") {
                preg_match('/^\d*_/', $item['superior'], $matches);
                if (!empty($matches)) {
                    $pid = trim($matches[0], "_");
                }
            }
            if (!empty($data[$pid])) {
                $data[$pid]['children'][] = &$data[$item['id']];
            } else {
                $tree[] = &$data[$item['id']];
            }
        }
        return $tree;
    }

    /**
     * 查找我的团队
     * @param $uid
     * @return array
     */
    public static function RelationList($uid)
    {
        $userData = $data = [];
        $user = User::find()->select(['uid', 'nickname', 'mobile', 'realname', 'superior', 'outs'])->where(['uid' => $uid])->asArray()->one();
        $temp = User::find()->select(['uid', 'nickname', 'mobile', 'realname', 'superior', 'outs'])->where(" superior REGEXP '^{$uid}_|_{$uid}_' ")->asArray()->all();
        array_push($temp, $user);
        foreach ($temp as $key => $val) {
            $val["text"] = "<span id='{$val['nickname']}'>" . $val['realname'] . "</span><e style='color:#aaa;font-size:15px' id='{$val['uid']}'>&lt;" . 'HC' . substr($val['mobile'], -6) . "&gt;</e>" . '出局次数:<e style=\'color:#aaa;font-size:15px\'>&lt;' . $val['outs'] . '&gt;</e>';
            $val["id"] = $val['uid'];
            $userData[$val['uid']] = $val;
        }
        $data = self::disTree($userData);
        return $data;
    }

    /**
     * 淘宝推广位
     * @throws \yii\db\Exception
     */
    public function alimmPid()
    {
        $list = AdvertSpace::find()
            ->where(['type' => AdvertSpace::TYPE_TB, 'status' => 1])
            ->orderBy('id ASC')
            ->limit(1)
            ->one();
        if (empty($list)) {
            throw new \yii\db\Exception('淘宝广告位创建失败:广告位不足,请联系管理员新增推广位');
        }
        $list->updateAttributes([
            'status' => 2,
            'uid' => self::getId(),
        ]);
        $this->alimm_pid = $list['pid'];
    }

    /**
     * 京东推广位
     * @throws \yii\db\Exception
     */
    public function jdPid()
    {
        $list = AdvertSpace::find()
            ->where(['type' => AdvertSpace::TYPE_JD, 'status' => 1])
            ->orderBy('id ASC')
            ->limit(1)
            ->one();
        if (empty($list)) {
            throw new \yii\db\Exception('京东广告位创建失败:广告位不足,请联系管理员新增推广位');
        }
        $list->updateAttributes([
            'status' => 2,
            'uid' => self::getId(),
        ]);
        $this->jd_pid = $list['pid'];
    }

    /**
     * 拼多多
     * @throws \yii\db\Exception
     */
    public function pddPid()
    {
        $list = AdvertSpace::find()
            ->where(['type' => AdvertSpace::TYPE_PDD, 'status' => 1])
            ->orderBy('id ASC')
            ->limit(1)
            ->one();
        if (empty($list)) {
            throw new \yii\db\Exception('拼多多广告位创建失败:广告位不足,请联系管理员新增推广位');
        }
        $list->updateAttributes([
            'status' => 2,
            'uid' => self::getId(),
        ]);
        $this->pdd_pid = $list['pid'];
    }

    /**
     * @param $invite_code
     * @throws \yii\db\Exception
     */
    public function validateReferrer($invite_code)
    {
        //验证邀请码
        $referrer = self::findByInviteCode($invite_code);
        if (!$referrer) {
            throw new \yii\db\Exception('创建用户失败:邀请码不存在');
        }
        $this->referrer = (string)$referrer->mobile;
        $this->superior = $referrer->uid . '_' . $referrer->superior;
    }

}

<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/26 13:50
 */

namespace backend\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "th_system_user_log".
 *
 * @property string $id
 * @property string $ip
 * @property string $username
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 */
class SystemUserLog extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'status'], 'required'],
            [['status'], 'integer'],
            [['username'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => '登录IP',
            'username' => '登录用户名',
            'status' => '状态',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    public function behaviors()
    {
        return [
            'class' => TimestampBehavior::className()
        ];
    }

    public function write($username, $status = 0)
    {
        $this->ip = \Yii::$app->request->userIP;
        $this->username = $username;
        $this->status = $status;
        return $this->save();
    }

    public function check($username)
    {
        $ip = \Yii::$app->request->userIP;
        $num = self::find()->where(['username' => $username, 'ip' => $ip, 'status' => 0])->andWhere(['>=', 'created_at', time() - 7200])->count();
        return $num;
    }
}

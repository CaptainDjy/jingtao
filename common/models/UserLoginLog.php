<?php
/**
 * Created by PhpStorm.
 * User: XingBin
 * Date: 2017/9/1
 * Time: 9:58
 */

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property int $id [int(10) unsigned]
 * @property string $ip [varchar(50)]
 * @property string $mobile [varchar(32)]  手机号
 * @property bool $status [tinyint(1) unsigned]  状态1:登陆成功，0:登陆失败
 * @property string $msg [varchar(255)]  log提示
 * @property int $created_at [int(11) unsigned]  登录时间
 */
class UserLoginLog extends ActiveRecord
{

    public function rules()
    {
        return [];
    }

    public function attributeLabels()
    {
        return [
            'id' => '操作编号',
            'mobile' => '用户手机号',
            'ip' => '登陆ip',
            'msg' => '操作内容',
            'created_at' => '操作时间'
        ];
    }

    public function addUserLoginLogs($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        $this->created_at = time();
        return $this->save();
    }

}

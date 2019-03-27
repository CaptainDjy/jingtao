<?php
namespace api\controllers;
use api\alisms\SendSms;
use api\models\user;
use api\models\AdvertSpace;
use Yii;

/**
 *
 */
class UserController extends ControllerBase {
/**
 *短信验证
 */
	public function actionVerificationCode() {
		$request = \Yii::$app->request;
		$username = $request->post('mobile');
		if (empty($username)) {
			return $this->responseJson(0, $username, '手机号不能为空');
		}

		// die();
		// exit();
		// (new MobilePhoneNumber())->goCheck();
		//获取对象，如果上面没有引入命名空间，可以这样实例化：$sms = new \alisms\SendSms()
		$sms = new SendSms();
		//$mobile为手机号
		$mobile = $username;
		//模板参数，自定义了随机数，你可以在这里保存在缓存或者cookie等设置有效期以便逻辑发送后用户使用后的逻辑处理
		$code = mt_rand(100000, 999999);

		// $source = Yii::$app->cache->get('mobile');
		$templateParam = array("code" => $code);
		$m = $sms->send($mobile, $templateParam);
		//类中有说明，默认返回的数组格式，如果需要json，在自行修改类，或者在这里将$m转换后在输出
		if ($m['Code'] == 'OK') {

			// if (!$result) {
			// 	throw new Exception('缓存写入失败,系统内部错误', 500);
			// }
//			$emp = User::find()->where(['mobile' => $mobile])->asArray()->all();
			Yii::$app->cache->set($mobile, $code, 600);
			//验证码发送成功
			return $this->responseJson(1, '', '发送成功');

		}if ($m['Code'] == 'isv.BUSINESS_LIMIT_CONTROL') {
			//短信验证码发送失败
//			return json_encode(['code' => 204, 'data' => $m], 320);
            return $this->responseJson(101, '', '发送失败');
		}

	}

/**
 *
 * 注册用户
 *
 */

    public function actionAdd() {
        $model = new user();
        $request = \Yii::$app->request;
        $res = user::find()->select('mobile')->asArray()->all(); //查询所有手机号
        foreach ($res as $key => $value) {
            if ($request->post('mobile') == $value['mobile']) {
                return $this->responseJson(101, '101', '手机号已存在');
            }
        }

        if ($request->post('mobile') != $value['mobile']) {
            $model->mobile = $request->post('mobile');
            $password=$request->post('password');
            $model->password_hash = Yii::$app->security->generatePasswordHash($password, 6);
            if ($request->post('yzm') == null) {
                return $this->responseJson(102, '102', '验证码不能为空');
            }
            if (Yii::$app->cache->get($request->post('mobile')) == $request->post('yzm')) {
            $model->userid = rand(10000, 99999); //自动生成用户ID
            $model->generalize = substr(md5(time()), 0, 7); //用户推广码
//                $model->lv = 1; //会员等级
            $model->created_at=time();
            $model->avatar='uploads/avatar/avatar.png';
            $model->invite_code = $request->post('invite'); //邀请码
            $alimmpid=AdvertSpace::find()->where(['uid'=>null])->asArray()->one();
            $model->alimm_pid=$alimmpid['pid'];//淘宝联盟推广位
            $model->jd_pid='';
            $model->pdd_pid='';
            $superior=user::find()->select(['uid','superior','generalize'])->where(['generalize'=>$request->post('invite')])->asArray()->one();
            $model->superior=$superior['uid'].'_';//上一级   uid_
            $invit = $request->post('invite');
//				print_r($invit);
//				exit;
            //邀请码为空注册
            if ($request->post('invite')==null){
                $model->save();
                $uid=User::findBySql('select * from dh_user order by uid desc limit 1')->asArray()->one();
                $test=AdvertSpace::find()->where(['pid'=>$alimmpid['pid']])->one();
//                print_r($test);
//                exit;
                $test->uid=$uid['uid'];
                $test->save();
                return $this->responseJson(1, '1', '注册成功');
            }

            //邀请码存在注册
            $invite = user::find()->where(['=', 'generalize', $invit])->asArray()->all(); //查询推广码
//				print_r($invite);
//				die();
            if ($invite) {
                $model->save();
                return $this->responseJson(1, '1', '注册成功');
            }else{
                return $this->responseJson(103, '103', '邀请码错误');
            }
        } else {
            return $this->responseJson(104, '104', '注册失败手机验证码错误');
        }
    }
    }

/**
 *
 * 忘记密码
 */
	public function actionForget() {
		$model = new user();
		$request = \Yii::$app->request;
		$res = user::find()->where(['mobile' => $request->post('mobile')])->one(); //查询数据库是否有此手机号
		if ($res) {
			if ($request->post('yzm') == null) {
				return $this->responseJson(0, '', '验证码不能为空');
			}
			if (Yii::$app->cache->get($request->post('mobile')) == $request->post('yzm')) {
				$res->password_hash = md5($request->post('password'));
				$res->save();
				return $this->responseJson(1, '', '修改成功');
			} else {
				return $this->responseJson(0, '', '修改失败');
			}

		} else {
			return $this->responseJson(0, '', '此手机号还没有注册');
		}
	}

/**
 *
 * 用户登录
 */
	public function actionLogin() {
		$request = \Yii::$app->request;
		$mobile = $request->post('mobile');
		$password = md5($request->post('password'));
		$user = user::find()->select('uid,mobile, password_hash')->where(['mobile' => $mobile, 'password_hash' => $password])->asArray()->one();
		if ($user) {
			return $this->responseJson(1, $user['uid'], '登录成功');
		} else {
			return $this->responseJson(0, '', '登录失败');
		}
	}

/**
 *
 * 修改用户的信息(头像 昵称)
 */
	public function actionUpdate() {
		$request = \Yii::$app->request;
		$id = $request->post('uid');
		$nickname = $request->post('nickname');
		$user = user::find()->where(['uid' => $id])->one();

//        $avatar='public\\uploads\\avatar\\'.$_FILES['avatar']['name'];
		$user->nickname = $nickname;
//        print_r($avatar);
		//        die();

		//判断上传的文件是否出错,是的话，返回错误
		if ($_FILES["avatar"]["error"]) {
			return $this->responseJson(0, '', '上传失败');
//            echo $_FILES["avatar"]["error"];
		} else {
			//没有出错
			//加限制条件
			//判断上传文件类型为png或jpg且大小不超过1024000B
			if (($_FILES["avatar"]["type"] == "image/png" || $_FILES["avatar"]["type"] == "image/jpeg") && $_FILES["avatar"]["size"] < 1024000) {
				//防止文件名重复
				$filename = "../../public/uploads/avatar/" . time() . $_FILES["avatar"]["name"];
				//转码，把utf-8转成gb2312,返回转换后的字符串， 或者在失败时返回 FALSE。
				$filename = iconv("UTF-8", "gb2312", $filename);
				//检查文件或目录是否存在
				if (file_exists($filename)) {
					return $this->responseJson(0, '', '该文件已存在');
//                    echo"该文件已存在";
				} else {
					//保存文件,   move_uploaded_file 将上传的文件移动到新位置
					move_uploaded_file($_FILES["avatar"]["tmp_name"], $filename); //将临时地址移动到指定地址
				}
			} else {
				return $this->responseJson(0, '', '文件类型不对');
//                echo"文件类型不对";
			}
		}
//        print_r(substr($filename,6,100));
		//        exit;
		$user->avatar = substr($filename, 6, 200); //修改头像
		//        exit;
		$user->save();
		return $this->responseJson(1, $user->save(), '修改成功');

	}

/**
 *
 * 根据uid获取个人信息
 */
	public function actionUser() {
		$request = \Yii::$app->request;
		$id = $request->post('id');
		$res = User::find()->where(['uid' => $id])->asArray()->all();
		return $this->responseJson(1, $res, '用户信息');
	}

    // $session = \Yii::$app->session;
    // $session->set("mobile", 13888888888); //设置
    // // $session->remove("mobile");//清除
    // $mobile = $session['mobile']; //获取

/**
 * 根据uid查询个人订单
 */
	public function actionOrder() {
        $uid = Yii::$app->user->id;
        print_r($uid);
        exit;
		$request = \Yii::$app->request;
//		$id = $request->post('id');
		$user = User::find()->where(['uid' => $id])->one();
		// $order = $user->hasMany(order::classname(), ['uid' => 'uid'])->asArray()->all();
		// $order = $user->getOrder();
		$order = $user->order; //同上一样的效果
		return $this->responseJson(1, $order, '订单信息');
	}
/**
* 根据uid粉丝信息
*/
    public function actionReferrer() {
        $request = \Yii::$app->request;
        $id = $request->post('id');
        $user = User::find()->select(['mobile','nickname','generalize'])->where(['uid' => $id])->Asarray()->all();
        $referrer=$user[0]['generalize'];//当前登录人的推荐码
        $referrer2 = User::find()->select(['mobile','nickname','generalize'])->where(['invite_code' => $referrer])->Asarray()->all();
//        print_r($referrer2);
        return $this->responseJson(1, $referrer2, '粉丝信息');
    }

/**
 * 根据uid查询个人订单(待付款)
 */
	public function actionOrderstay() {

		$request = \Yii::$app->request;
		$id = $request->post('id');
		$type = $request->post('type');
		$user = User::find()->where(['uid' => $id])->one();
		$order = $user->getOrderstay($type);
		return $this->responseJson(1, $order, '待付款订单信息');
	}
/**
 * 根据uid查询个人订单(已付款)
 */
	public function actionOrderend() {

		$request = \Yii::$app->request;
		$id = $request->post('id');
		$type = $request->post('type');
		$user = User::find()->where(['uid' => $id])->one();
		$order = $user->getOrderend($type);
		return $this->responseJson(1, $order, '已付款订单信息');
	}
/**
 * 根据uid查询个人订单(已完成)
 */
	public function actionOrderdone() {

		$request = \Yii::$app->request;
		$id = $request->post('id');
		$type = $request->post('type');
		$user = User::find()->where(['uid' => $id])->one();
		$order = $user->getOrderdone($type);
		return $this->responseJson(1, $order, '已完成订单信息');
	}
/**
 * 根据uid查询个人订单(已退款)
 */
	public function actionOrderfade() {
		$request = \Yii::$app->request;
		$type = $request->post('type');
		$id = $request->post('id');
		$user = User::find()->where(['uid' => $id])->one();
		$order = $user->getOrderfade($type);
		return $this->responseJson(1, $order, '已退款订单信息');
	}
// /**
	//  *根据订单查用户
	//  */
	// 	public function actionName() {
	// 		//订单id为1的用户信息
	// 		$order = order::find()->where(['id' => 1])->one();
	// 		$user = $order->user;
	// 		return $this->responseJson(1, $user, '用户信息');
	// 	}

}
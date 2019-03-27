<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/5
 * Time: 9:09
 */

namespace api\modules\amoy\controllers;


use common\models\Config;
use yii\helpers\Url;

class SystemController extends ControllerBase
{
    /**
     * 淘宝openUid 转换
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionOpenuidChange()
    {
        $data = [
            'method' => 'taobao.openuid.change',
            'open_uid' => '',
            'target_app_key' => '1',
        ];
        $result = $this->getUrlResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['openuid_change_response']['new_open_uid'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 刷新Access Token
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTokenRefresh()
    {
        $data = [
            'method' => 'taobao.top.auth.token.refresh',
            'refresh_token' => '',
        ];
        $result = $this->getUrlResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['top_auth_token_refresh_response']['token_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 获取Access Token
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTokenCreate()
    {
        $data = [
            'method' => 'taobao.top.auth.token.create',
            'code' => '',
        ];
        $result = $this->getUrlResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['top_auth_token_create_response']['token_result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 获取开放平台出口IP段
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIpoutGet()
    {
        $data = [
            'method' => 'taobao.top.ipout.get',
        ];
        $result = $this->getUrlResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['top_ipout_get_response']['ip_list'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 获取TOP通道解密秘钥
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSecretGet()
    {
        $data = [
            'method' => 'taobao.top.secret.get',
            'random_num' => '',
        ];
        $result = $this->getUrlResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['top_secret_get_response'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 注册加密账号
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSecretRegister()
    {
        $data = [
            'method' => 'taobao.top.secret.register',
            'random_num' => '',
        ];
        $result = $this->getUrlResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['top_secret_register_response']['result'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * sdk信息回调
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionFeedback()
    {
        $data = [
            'method' => 'taobao.top.sdk.feedback.upload',
            'type' => '1',
        ];
        $result = $this->getUrlResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['top_sdk_feedback_upload_response']['upload_interval'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionOpenuidGet()
    {
        $data = [
            'method' => 'taobao.openuid.get',
        ];
        $result = $this->getUrlResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['openuid_get_response']['open_uid'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 通过mixnick转换openuid
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetBymixnick()
    {
        $data = [
            'method' => 'taobao.openuid.get.bymixnick',
            'mix_nick' => '淘012xBbgHy7GJqSgfChglzpj82DEMds1FAQI7fgfrP3PMg=',
        ];
        $result = $this->getUrlResult($data);
        if (empty($result['sub_code'])) {
            $arr = $result['openuid_get_bymixnick_response']['open_uid'];
            return $this->responseJson(0, $arr, '查询数据成功');
        } else {
            return $this->responseJson(1, '', $result['sub_msg']);
        }
    }

    /**
     * 检查APP更新
     */
    public function actionCheck()
    {
        $version = \Yii::$app->request->post('version');
        $type = \Yii::$app->request->post('type');

        if ($type == 'android') {
            $newVersion = Config::getConfig('APP_VERSIONS');
        } else if ($type == 'ios') {
            $newVersion = Config::getConfig('APP_VERSIONS_IOS');
        } else {
            return $this->responseJson(1, '', '设备类型未知');
        }

        $urlStr = sprintf('/hotcode/%s/%s/update.wgt', $type, $newVersion);
        if (!file_exists(\Yii::getAlias('@public' . $urlStr))) {
            return $this->responseJson(1, '', '文件暂无');
        }
        $newVersionUrl = Url::to($urlStr, true);

        if (version_compare($version, $newVersion, '>=')) {
            return $this->responseJson(1, '', '暂无');
        }

        return $this->responseJson(0, ['downUrl' => $newVersionUrl, 'version' => $newVersion], '发现新版本');
    }
}

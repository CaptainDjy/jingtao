<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/20 9:19
 */

namespace common\behaviors;

use yii\filters\auth\AuthMethod;

class HttpTokenAuth extends AuthMethod
{

    public $uid = null;
    /**
     * @var string the HTTP authentication realm
     */
    public $realm = 'api';


    /**
     * @param \yii\web\User $user
     * @param \yii\web\Request $request
     * @param \yii\web\Response $response
     * @return null|\yii\web\IdentityInterface
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get('token');
        if ($authHeader !== null) {
            $identity = $user->loginByAccessToken($authHeader, get_class($this));
            if ($identity === null) {
                $this->handleFailure($response);
            }
            $this->uid = $identity->getId();
            return $identity;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function challenge($response)
    {
        $response->headers->set('WWW-Authenticate', "Bearer realm=\"{$this->realm}\"");
    }

    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        echo json_encode([
            'code' => 403,
            'data' => [],
            'msg' => '访问超时或未授权！'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

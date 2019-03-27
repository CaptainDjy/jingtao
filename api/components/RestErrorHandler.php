<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/10/10 10:36
 */

namespace api\components;


use yii\base\UserException;
use yii\db\Exception;
use yii\web\ErrorHandler;
use yii\web\Response;

class RestErrorHandler extends ErrorHandler
{
    /**
     * @param \Error|\Exception $exception
     */
    protected function renderException($exception)
    {
        if (\Yii::$app->has('response')) {
            $response = \Yii::$app->getResponse();
            // reset parameters of response to avoid interference with partially created response data
            // in case the error occurred while sending the response.
            $response->isSent = false;
            $response->stream = null;
            $response->data = null;
            $response->content = null;
        } else {
            $response = new Response();
        }
        $response->format = Response::FORMAT_JSON;
        $response->setStatusCodeByException($exception);

        $response->data = ['code' => 1, 'data' => '', 'msg' => $exception->getMessage()];
        if (YII_DEBUG) {
            $data['type'] = get_class($exception);
            if (!$exception instanceof UserException) {
                $data['file'] = $exception->getFile();
                $data['line'] = $exception->getLine();
                $data['stack-trace'] = explode("\n", $exception->getTraceAsString());
                if ($exception instanceof Exception) {
                    $data['error-info'] = $exception->errorInfo;
                }
            }
            $response->data['data'] = $data;
        }
        //var_dump($response->data);
        echo PHP_EOL;
        echo \GuzzleHttp\json_encode($response->data);
        //fwrite(fopen('../runtime/logs/error.log','w+'),$response->data);
        exit();
        $response->send();
    }
}

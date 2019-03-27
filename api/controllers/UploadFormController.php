<?php
namespace api\controllers;
use Yii;
use yii\web\Controller;
use api\models\UploadForm;
use yii\web\UploadedFile;

class UploadFormController extends Controller
{
    public function actionUpload()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($model->upload()) {
                // 文件上传成功
                return;
            }
        }

        return $this->render('upload', ['model' => $model]);
    }
}
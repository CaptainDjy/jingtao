<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/18
 * Time: 15:28
 */
namespace api\modules\amoy\controllers;
use common\models\Article;
use common\models\ArticleCategory;
class ArticleController extends ControllerBase
{
//文章内容[关于我们]
    public function actionArticle()
    {
        $article = Article::find()->where(['cid'=>1,'status'=>1])->asArray()->all();
        return $this->responseJson(200,$article,'返回数据成功');

    }

//文章内容[系统通知]
    public function actionArticlei()
    {
        $article = Article::find()->where(['cid'=>5,'status'=>1])->asArray()->all();
        return $this->responseJson(200,$article,'返回数据成功');

    }
//文章分类
    public function actionArticlecate()
    {
        $articlecate = ArticleCategory::find()->asArray()->all();
        return $this->responseJson(200,$articlecate,'返回数据成功');

    }

}
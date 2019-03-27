<?php

use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use common\helpers\Utils;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;


/**
 * @var $this yii\web\View
 * @var $query
 * @var $keywords array
 */
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box box-success">
                <div class="box-header with-border"></div>
                <div class="box-body">
                    <form class="form-horizontal" method="get">
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">关键词</label>
                            <div class="col-xs-12 col-sm-5">
                                <input type="hidden" name="r" value="announcement/system">
                                <input type="text" class="form-control" name="keywords" value="<?= $keywords ?>"
                                       placeholder="请输入ID或标题搜索">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                            <div class="col-xs-12 col-sm-5">
                                <button type="submit" class="btn btn-info">搜索</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="box-header clearfix no-border">
                <a href="<?= Url::toRoute(['announcement/update']) ?>" class="btn btn-default pull-left"><i
                            class="fa fa-plus"></i>添加系统公告</a>
            </div>
            <?php
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]);
            echo MyGridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'title',
                    ],
                    [
                        'attribute' => 'remark',
                        'value' => function ($model) {
                            return Utils::cutStr($model->remark,30);
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'html',
                        'value' => function ($model) {
                            return $model['status'] == 9 ? '<span class="badge bg-red">禁用</span>' : '<span class="badge bg-green">启用</span>';
                        },
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                        'enableSorting' => true,
                    ],
                    [
                        'class' => MyActionColumn::class,
                    ],
                ]
            ]);
            ?>
        </div>
    </div>
</div>


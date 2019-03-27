<?php

use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use common\helpers\Utils;
use common\widgets\daterangepicker\DateRangePicker;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchArr array
 */

$this->title = '商家管理';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs-cate'); ?>
    <div class="tab-content">

        <div class="box box-success">
            <div class="box-header with-border">
                搜索
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <?= Html::beginForm(['biz/cate'], 'get', ['class' => 'form-horizontal']) ?>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">分类名称</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-4">
                        <?= Html::textInput('title', $searchArr['title'], ['class' => 'form-control', 'placeholder' => '请输入分类名']); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-4">
                        <?= Html::submitButton('搜索', ['class' => 'btn btn-success  pull-right']); ?>
                        <a href="<?= Url::toRoute(['biz/cate']) ?>">
                            <div class="btn btn-default"><i class="glyphicon glyphicon-leaf"></i> 清除筛选</div>
                        </a>
                    </div>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <div class="tab-pane active">
            <?= MyGridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'headerOptions' => ['width' => '80'],
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'pic',
                        'headerOptions' => ['width' => '100'],
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::img(Utils::toMedia($model->pic),
                                [
                                    'class' => 'img-rectangle',
                                    'height' => 50,
                                    'width' => 50
                                ]
                            );
                        },
                    ],
                    [
                        'attribute' => 'title',
                        'value' => function ($model) {
                            return Utils::cutStr($model->title);
                        },
                    ],
                    [
                        'attribute' => 'created_at',
                        'headerOptions' => ['width' => '200'],
                        'enableSorting' => true,
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                    ],
                    [
                        'class' => MyActionColumn::class,
                        'headerOptions' => ['width' => '150'],
                        'header' => '操作',
                        'template' => '{cate-edit}&nbsp;&nbsp;{cate-del}',
                        'buttons' => [
                            'cate-edit' => function ($url) {
                                return Html::a('编辑', $url, ['title' => '编辑']);
                            },
                            'cate-del' => function ($url) {
                                return Html::a('删除', $url, ['title' => '删除', 'data-confirm' => '确认删除?操作不可恢复!']);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>

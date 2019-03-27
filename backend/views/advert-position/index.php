<?php

use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use common\helpers\Utils;
use common\models\AdvertPosition;
use common\widgets\daterangepicker\DateRangePicker;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $dataProvider array
 * @var $searchArr array
 */

?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
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
                <?= Html::beginForm(['advert-position/index'], 'get', ['class' => 'form-horizontal']) ?>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">标题</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-4">
                        <?= Html::textInput('title', $searchArr['title'], ['class' => 'form-control', 'placeholder' => '请输入标题']); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-4">
                        <?= Html::submitButton('搜索', ['class' => 'btn btn-success  pull-right']); ?>
                        <a href="<?= Url::toRoute(['advert-position/index']) ?>">
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
                        'attribute' => 'img',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::img(Utils::toMedia($model->img),
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
                        'attribute' => 'op',
                        'headerOptions' => ['width' => '100'],
                    ],
                    [
                        'label' => '尺寸',
                        'attribute' => 'size',
                        'headerOptions' => ['width' => '100'],
                        'value' => function ($model) {
                            return $model->width . ' × ' . $model->height;
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'headerOptions' => ['width' => '100'],
                        'value' => function ($model) {
                            return AdvertPosition::STATUS_LABEL[$model->status];
                        },
                    ],
                    [
                        'attribute' => 'remark',
                        'format' => 'raw',
                        'headerOptions' => ['width' => '280'],
                        'value' => function ($model) {
                            return Utils::cutStr($model->remark,15);
                        },
                    ],
                    [
                        'attribute' => 'created_at',
                        'headerOptions' => ['width' => '180'],
                        'enableSorting' => true,
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                    ],
                    [
                        'class' => MyActionColumn::class,
                        'headerOptions' => ['width' => '180'],
                        'header' => '操作',
                        'template' => '{edit}&nbsp;&nbsp;{del}',
                        'buttons' => [
                            'edit' => function ($url) {
                                return Html::a('编辑', $url, ['title' => '编辑']);
                            },
                            'del' => function ($url) {
                                return Html::a('删除', $url, ['title' => '删除', 'data-confirm' => '确认删除?操作不可恢复!']);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>


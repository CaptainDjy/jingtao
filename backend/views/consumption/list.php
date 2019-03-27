<?php

/**
 * @var $this yii\web\View
 * @var $query yii\db\ActiveQuery
 * @var $searchArr array
 */


use backend\widgets\grid\MyGridView;
use common\helpers\Utils;
use common\models\Consumption;
use common\widgets\daterangepicker\DateRangePicker;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="box box-success">
            <div class="box-header with-border">
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>

            <div class="box-body">
                <?= Html::beginForm(['consumption/list'], 'get', ['class' => 'form-horizontal']) ?>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">关键词</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= Html::textInput('keywords', $searchArr['keywords'], ['class' => 'form-control', 'placeholder' => "ID 、用户ID"]); ?>
                        <div class="help-block">请输入关键字</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">时间</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= DateRangePicker::widget([
                            'name' => 'date',
                            'clientOptions' => [
                                'startDate' => $searchArr['date']['start'],
                                'endDate' => $searchArr['date']['end'],

                            ],
                            'clientEvents' => [],
                        ]) ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= Html::submitButton('提交', ['class' => 'btn btn-success']); ?>
                        <a href="<?= Url::toRoute(['/consumption/list']) ?>">
                            <div class="btn btn-default"><i class="glyphicon glyphicon-leaf"></i> 清除筛选</div>
                        </a>
                        <?= Html::submitButton('导出商家列表', ['class' => 'btn btn-primary pull-right', 'name' => 'op', 'value' => 'export']); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">统计</label>
                    <div class="col-xs-4 col-sm-4 col-md-2 col-lg-2">
                        <div class="input-group">
                            <span class="input-group-addon">实缴金额</span>
                            <input name="" value="<?= $total ?>" class="form-control" placeholder="请输入整数" disabled>
                            <span class="input-group-addon">元</span>
                        </div>
                    </div>
                </div>

                <?= Html::endForm() ?>
            </div>
        </div>

        <div class="tab-pane active">
            <?php
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]);
            $arr = $query->all();
            echo MyGridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'uid',
                        'enableSorting' => true,
                    ],
                    'order_id',
                    [
                        'attribute' => 'consumption',
                        'enableSorting' => true,
                        'value' => function ($model) {
                            return Consumption::partnerConsumption($model->consumption);
                        }
                    ],
                    [
                        'attribute' => 'level',
                        'enableSorting' => true,
                    ],
                    'province', 'city', 'county',
                    [
                        'attribute' => 'price',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'pay_order',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a(Html::img(Utils::toMedia($model->pay_order),
                                ['class' => 'img-rectangle',
                                    'height' => 50, 'width' => 100]
                            ), ['/article/big-pic', 'id' => $model->pay_order], ['title' => '放大图片', 'data-toggle' => 'modal', 'data-target' => '#ajaxModal']) ?: '';
                        },
                    ],
                    [
                        'attribute' => 'remark',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'html',
                        'value' => function ($model) {
                            return Consumption::partnerStatus($model->status);
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
                        'class' => \backend\widgets\grid\MyActionColumn::class,
                        'template' => '{update}&nbsp;&nbsp;{reject}&nbsp;&nbsp;{agree}',
                        'buttons' => [
                            'update' => function ($url) {
                                return Html::a('<span class=" glyphicon glyphicon-pencil"></span>', $url, ['title' => '修改']);
                            },

                            'agree' => function ($url, $model) {
                                return $model->status == 1 ?
                                    Html::a('<span class="glyphicon glyphicon-ok-circle"></span>', $url, ['title' => '审核', 'onclick' => "if(!confirm('请确认审核成功吗？')) return false;"]) : '';
                            },
                            'reject' => function ($url, $model) {
                                return ($model->status > 0 && $model->status != 3) ?
                                    Html::a('<span class="glyphicon glyphicon-remove-circle"></span>', $url, ['title' => '审核驳回', 'data-toggle' => 'modal', 'data-target' => '#ajaxModal']) : '';
                            },
                        ],
                    ],
                ]
            ]);
            ?>
        </div>
    </div>
</div>



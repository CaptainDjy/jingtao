<?php

/**
 * @var $this yii\web\View
 * @var $query yii\db\ActiveQuery
 * @var $searchArr array
 * @var string $todaycount
 * @var string $yesdaycount
 */


use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use common\helpers\MyHtml;
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
                <?= Html::beginForm(['user/list'], 'get', ['class' => 'form-horizontal']) ?>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">关键词</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= Html::textInput('keywords', $searchArr['keywords'], ['class' => 'form-control', 'placeholder' => "UID、昵称"]); ?>
                        <div class="help-block">请输入关键字</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">时间</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?=
                        DateRangePicker::widget([
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
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">统计</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        一共<span class="badge bg-green"><?= $count ?></span>人;今日注册用户为:<span class="badge bg-green"><?=
                            $todaycount ?></span>人;昨日注册用户为:<span class="badge bg-green"><?=
                            $yesdaycount ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= Html::submitButton('提交', ['class' => 'btn btn-success']); ?>
                        <a href="<?= Url::toRoute(['user/list']) ?>">
                            <div class="btn btn-default"><i class="glyphicon glyphicon-leaf"></i> 清除筛选</div>
                        </a>
                        <!--                        < ?= Html::submitButton('导出会员列表', ['class' => 'btn btn-primary pull-right', 'name' => 'op', 'value' => 'export']); ?>-->
                    </div>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <div class="box-header clearfix no-border">
            <a href="<?= Url::toRoute(['user/update']) ?>" class="btn btn-primary pull-left">
                <i class="fa fa-plus"></i> 添加会员
            </a>
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
                        'attribute' => 'uid',
                        'enableSorting' => true,
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'nickname',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $str = empty($model->nickname) ? $model->nickname : $model->nickname;
                            return $str;
                        }
                    ],

                    [
                        'attribute' => 'mobile',
                    ],
                    [
                        'attribute' => 'lv',
                    ],
//                    [
//                        'attribute' => 'credit',
//                    ],
//                    [
//                        'attribute' => 'credit1',
//                    ],
//                    [
//                        'attribute' => 'credit2',
//                    ],
//                    [
//                        'attribute' => 'credit3',
//                    ],
                    [
                        'attribute' => 'credit4',
                    ],
                    [
                        'attribute' => 'recommend',
                    ],

                    [
                        'label' => '注册时间',
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                    ],
//                    [
//                        'attribute' => 'status',
//                        'label' => '账户状态',
//                        'format' => 'raw',
//                        'value' => function ($model) {
//                            return $model->status == 0 ? '<span class="badge bg-green">正常</span>' : '<span class="badge bg-red">冻结</span>';
//                        }
//                    ],
                    [
                        'class' => MyActionColumn::class,
                        'template' => '{update}&nbsp;&nbsp;{delete}',
                        'headerOptions' => ['width' => '80'],
                    ],
                ]
            ]);
            ?>
        </div>
    </div>
</div>



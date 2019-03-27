<?php

/**
 * @var $this yii\web\View
 * @var $query yii\db\ActiveQuery
 * @var $searchArr array
 */

use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use common\widgets\daterangepicker\DateRangePicker;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="<?= Url::to(['user/recycle']) ?>">会员管理</a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="box box-success">
            <div class="box-header with-border">
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <?= Html::beginForm(['user/recycle'], 'get', ['class' => 'form-horizontal']) ?>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">关键词</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= Html::textInput('keywords', $searchArr['keywords'], ['class' => 'form-control', 'placeholder' => "用户id、昵称、姓名、手机号"]); ?>
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
                        <a href="<?= Url::toRoute(['user/recycle']) ?>">
                            <div class="btn btn-default"><i class="glyphicon glyphicon-leaf"></i> 清除筛选</div>
                        </a>
                        <?= Html::submitButton('导出会员列表', ['class' => 'btn btn-primary pull-right', 'name' => 'op', 'value' => 'export']); ?>
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
                        'attribute' => 'uid',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'nickname',
                        'enableSorting' => true,
                        'value' => function ($model) {
                            $str = '';
                            $str .= empty($model->nickname) ? '未完善' : $model->nickname;
                            return $str;
                        }
                    ],
                    [
                        'attribute' => 'realname',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'mobile',
                        'enableSorting' => true,
                    ],
//                    [
//                        'attribute' => 'credit0',
//                        'enableSorting' => true,
//                    ],
//                    [
//                        'attribute' => 'credit1',
//                        'enableSorting' => true,
//                    ],
//                    [
//                        'attribute' => 'round',
//                        'enableSorting' => true,
//                    ],
//                    [
//                        'attribute' => 'victory',
//                        'enableSorting' => true,
//                    ],
//                    [
//                        'attribute' => 'odds',
//                        'enableSorting' => true,
//                    ],
//                    [
//                        'attribute' => 'card_num',
//                        'enableSorting' => true,
//                    ],
                    [
                        'attribute' => 'level',
                        'enableSorting' => true,
                        'value' => function ($model) {
                            if ($model->level == 1) {
                                $str = '代理商';
                            } elseif ($model->level == 0) {
                                $str = '普通会员';
                            } else {
                                $str = '未知';
                            }
                            return $str;
                        },
                    ],
                    [
                        'label' => 'QQ',
                        'attribute' => 'qq',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'gender',
                        'enableSorting' => true,
                        'value' => function ($model) {
                            $str = '';
                            if ($model->gender == '1') {
                                $str .= '男';
                            } else if ($model->gender == '0') {
                                $str .= '女';
                            } else {
                                $str .= '未知';
                            }
                            return $str;
                        },
                    ],
                    [
                        'attribute' => 'referrer',
                        'enableSorting' => true,
                    ],
                    [
                        'label' => '注册时间',
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                        'enableSorting' => true,
                    ],
                    [
                        'class' => MyActionColumn::class,
                        'template' => '{back}&nbsp;&nbsp;{redelete}',
                        'headerOptions' => ['width' => '80'],
                        'buttons' => [
                            'back' => function ($url) {
                                return Html::a('<span class="glyphicon glyphicon-ok-sign"></span>', $url, ['title' => '还原']);
                            },
                            'redelete' => function ($url) {
                                return Html::a('<span class="glyphicon glyphicon-remove-sign"></span>', $url, ['title' => '完全删除']);
                            },
                        ],
                    ],
                ]
            ]);
            ?>
        </div>
    </div>
</div>




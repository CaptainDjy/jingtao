<?php
/**
 * @var array $searchArr
 */

use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use common\widgets\daterangepicker\DateRangePicker;
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\Utils;

/**
 * @var $dataProvider array
 * @var $searchArr array
 */

$this->title = '订单列表';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_order_tabs'); ?>
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
                <?= Html::beginForm(['finance/order'], 'get', ['class' => 'form-horizontal']) ?>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">用户账号</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-4">
                        <?= Html::textInput('name', $searchArr['name'], ['class' => 'form-control', 'placeholder' => '请输入用户UID']); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">时间范围</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-4">
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
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-4">
                        <?= Html::submitButton('搜索', ['class' => 'btn btn-success  pull-right']); ?>
                        <a href="<?= Url::toRoute(['finance/order']) ?>">
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
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'uid',
                    ],
                    [
                        'attribute' => 'trade_id',
                    ],
                    [
                        'attribute' => 'title',
                        'value' => function ($model) {
                            return Utils::cutStr($model->title);
                        },
                    ],

                    [
                        'attribute' => 'order_status',
                        'value' => function ($model) {
                           /* if ($model->order_status == 3) {
                                $str = '订单结算';
                            } elseif ($model->order_status == 12) {
                                $str = '订单付款';
                            } elseif ($model->order_status == 13) {
                                $str = '订单失效';
                            }*/if($model->order_status == -1){
                                $str = '待付款';
                            }elseif($model->order_status == 0){
                                $str = '已付款';
                            }elseif($model->order_status == 1){
                                $str = '已付款';
                            }elseif($model->order_status == 4){
                                $str = '已失效(不可提现)';
                            }elseif($model->order_status == 2){
                                $str = '已完成';
                            }elseif($model->order_status == 5){
                                $str = '已结算';
                            }else{
                                $str ='订单成功';
                            }
                            return $str;
                        },
                    ],

                    [
                        'attribute' => 'type',
                        'value' => function ($model) {
                            if ($model->type == 1) {
                                $str = '天猫';
                            } elseif ($model->type == 2) {
                                $str = '京东';
                            } else {
                                $str = '拼多多';
                            }
                            return $str;
                        },
                    ],
                    [
                        'attribute' => 'created_at',
                        'enableSorting' => true,
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                    ],
                    [
                        'class' => MyActionColumn::class,
                        'header' => '操作',
                        'template' => '{edit}&nbsp;&nbsp;{del}',
                        'buttons' => [
//                            'edit' => function ($url) {
//                                return Html::a('编辑', $url, ['title' => '编辑']);
//                            },
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


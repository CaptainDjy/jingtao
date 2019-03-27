<?php
/**
 * @var array $searchArr
 */

use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use common\models\Withdraw;
use common\widgets\daterangepicker\DateRangePicker;
use yii\grid\CheckboxColumn;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $dataProvider array
 * @var $searchArr array
 */

$this->title = '提现管理';
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
                <?= Html::beginForm(['withdraw/index'], 'get', ['class' => 'form-horizontal']) ?>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">单号</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-4">
                        <?= Html::textInput('sn', $searchArr['sn'], ['class' => 'form-control', 'placeholder' => '提现单号']); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">用户</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-4">
                        <?= Html::textInput('uid', $searchArr['uid'], ['class' => 'form-control', 'placeholder' => '用户ID']); ?>
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
                        <a href="<?= Url::toRoute(['withdraw/index']) ?>">
                            <div class="btn btn-default"><i class="glyphicon glyphicon-leaf"></i> 清除筛选</div>
                        </a>
                    </div>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <div class="tab-pane active">
            <!--<div class="box-header clearfix no-border">-->
            <!--    <span class="btn btn-success pull-left" id="ratify" style="margin-right: 10px">-->
            <!--        <i class="fa fa-trash"></i> 批量通过-->
            <!--    </span>-->
            <!--    <span class="btn btn-danger pull-left" id="refuse">-->
            <!--        <i class="fa fa-trash"></i> 批量拒绝-->
            <!--    </span>-->
            <!--</div>-->
            <?= MyGridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'class' => CheckboxColumn::class,
                        'headerOptions' => [
                            'width' => '50',
                        ],
                    ],
                    [
                        'attribute' => 'id',
                        'headerOptions' => [
                            'width' => '80',
                        ],
                        'enableSorting' => true,
                    ],
                    [
                        'label' => '用户',
                        'attribute' => 'uid',
                        'headerOptions' => [
                            'width' => '80',
                        ],
                        'enableSorting' => true,
                    ],
//                    [
//                        'attribute' => 'trade_sn',
//                        'enableSorting' => true,
//                    ],
                    [
                        'attribute' => 'amount',
                        'headerOptions' => [
                            'width' => '180',
                        ],
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'pay_to',
                        'headerOptions' => [
                            'width' => '180',
                        ],
                    ],
//                    [
//                        'attribute' => 'alipay_date',
//                        'headerOptions' => [
//                            'width' => '180',
//                        ],
//                    ],
                    [
                        'attribute' => 'status',
                        'headerOptions' => [
                            'width' => '180',
                        ],
                        'enableSorting' => true,
                        'value' => function ($model) {
                            return Withdraw::STATUS_LABEL[$model->status];
                        },
                    ],
                    [
                        'attribute' => 'created_at',
                        'headerOptions' => [
                            'width' => '150',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                    ],
                    [
                        'attribute' => 'approve_at',
                        'headerOptions' => [
                            'width' => '180',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                    ],
//                    [
//                        'attribute' => 'msg',
//                        'headerOptions' => [
//                            'width' => '180',
//                            'class' => 'text-center',
//                        ],
//                        'contentOptions' => [
//                            'class' => 'text-center',
//                        ],
//                    ],
//                    [
//                        'attribute' => 'remark',
//                        'headerOptions' => [
//                            'width' => '180',
//                            'class' => 'text-center',
//                        ],
//                        'contentOptions' => [
//                            'class' => 'text-center',
//                        ],
//                    ],
                    [
                        'class' => MyActionColumn::class,
                        'headerOptions' => [
                            'width' => '150',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                        'header' => '操作',
                        'template' => '{ratify}&nbsp;&nbsp;{refuse}&nbsp;&nbsp;{remark}',
                        'buttons' => [
                            'ratify' => function ($url, $model) {
                                if ($model->status != Withdraw::STATUS_DEFAULT) {
                                    return '';
                                }
                                return Html::a('通过', $url, ['title' => '批准', 'data-confirm' => '批准后将转账至提现的支付宝账户,是否通过?']);
                            },
                            'refuse' => function ($url, $model) {
                                if ($model->status != Withdraw::STATUS_DEFAULT) {
                                    return '';
                                }
                                return Html::a('拒绝', $url, ['title' => '拒绝', 'style' => ['color' => 'red']]);
                            },
                            'remark' => function () {
                                return '';
                                // return Html::a('备注', $url, ['title' => '备注']);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>


<script>
    <?php $this->beginBlock('footerJs') ?>
    //function ratify(ids, type) {
    //    $.ajax({
    //        url: '<?//= Url::toRoute(['withdraw/examine'])?>//',
    //        method: 'POST',
    //        data: {ids: ids, type: type},
    //        success: function (res) {
    //            layer.closeAll();
    //            if (res.code === 0) {
    //                layer.msg(res.msg, {icon: 1, time: 1000}, function () {
    //                    layer.reload();
    //                });
    //            }
    //            else {
    //                layer.msg(res.msg, {icon: 2, time: 2000});
    //            }
    //        },
    //        error: function () {
    //            layer.closeAll();
    //            layer.msg('请求失败!', {icon: 2, time: 2000});
    //        }
    //    })
    //}
    //
    //$("#ratify").click(function () {
    //    layer.confirm('确定批量通过?', {icon: 3, title: '提示'}, function () {
    //        layer.load();
    //        var ids = $('#w1').yiiGridView('getSelectedRows');
    //        if (ids === null || ids === []) {
    //            layer.closeAll();
    //            layer.msg('请选择要通过的记录', {icon: 2, time: 2000});
    //        } else {
    //            ratify(ids, 'ratify');
    //        }
    //    });
    //});
    //
    //$("#refuse").click(function () {
    //    layer.confirm('确定批量拒绝?', {icon: 3, title: '提示'}, function () {
    //        layer.load();
    //        var ids = $('#w1').yiiGridView('getSelectedRows');
    //        if (ids === null || ids === []) {
    //            layer.closeAll();
    //            layer.msg('请选择要拒绝的记录', {icon: 2, time: 2000});
    //        } else {
    //            ratify(ids, 'refuse');
    //        }
    //    });
    //});

    <?php $this->endBlock() ?>
</script>


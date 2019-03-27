<?php
/**
 * @var array $searchArr
 */

use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use common\widgets\daterangepicker\DateRangePicker;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $dataProvider array
 * @var $searchArr array
 */

$this->title = '会员充值表';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_recharge_tabs'); ?>
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
                <?= Html::beginForm(['finance/recharge'], 'get', ['class' => 'form-horizontal']) ?>

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
                        <a href="<?= Url::toRoute(['finance/recharge']) ?>">
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
                        'attribute' => 'amount',
                    ],
                    [
                        'attribute' => 'type',
                        'value' => function ($model) {
                            if ($model->type == 1) {
                                $str = '微信';
                            } else {
                                $str = '支付宝';
                            }
                            return $str;
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            if ($model->status == -1) {
                                $str = '支付失败';
                            }elseif($model->status == 0){
                                $str = '待支付';
                            }else{
                                $str = '支付成功';
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


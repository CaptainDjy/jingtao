<?php
/**
 * @var array $searchArr
 */

use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use common\helpers\MyHtml;
use common\helpers\Utils;
use common\models\Goods;
use common\widgets\daterangepicker\DateRangePicker;
use yii\grid\CheckboxColumn;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $dataProvider array
 * @var $searchArr array
 */

$this->title = '商品管理';
?>

<div class="nav-tabs-custom">
    <?= $this->render('_tabs-stale'); ?>
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
                <?= Html::beginForm(['goods/stale'], 'get', ['class' => 'form-horizontal']) ?>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">商品名称</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-4">
                        <?= Html::textInput('title', $searchArr['title'], ['class' => 'form-control', 'placeholder' => '请输入商品名称']); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">商品来源</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-4">
                        <?= Html::checkboxList('type', $searchArr['type'], Goods::TYPE_LABEL, ['item' => [MyHtml::class, 'checkboxListItem']]); ?>
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
                        <?= Html::submitButton('搜索', ['class' => 'btn btn-dh  pull-right']); ?>
                        <a href="<?= Url::toRoute(['goods/stale']) ?>">
                            <div class="btn btn-default"><i class="glyphicon glyphicon-leaf"></i> 清除筛选</div>
                        </a>
                    </div>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <div class="tab-pane active">
            <div class="box-header clearfix no-border">
                <span class="btn btn-dh pull-left" id="select">
                    <i class="fa fa-trash"></i> 删除选中商品
                </span>
            </div>
            <?= MyGridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'class' => CheckboxColumn::class,
                        'headerOptions' => [
                            'width' => '50',
                        ]
                    ],
                    [
                        'attribute' => 'id',
                        'headerOptions' => [
                            'width' => '80',
                        ],
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'thumb',
                        'headerOptions' => [
                            'width' => '60',
                        ],
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::img(Utils::toMedia($model->thumb),
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
                        'headerOptions' => [
                            'width' => '280',
                            'class' => 'text-center',
                        ],
                        'value' => function ($model) {
                            return Utils::cutStr($model->title, 20);
                        },
                    ],
                    [
                        'attribute' => 'origin_price',
                        'headerOptions' => [
                            'width' => '80',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                    ],
                    [
                        'attribute' => 'coupon_price',
                        'headerOptions' => [
                            'width' => '80',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                    ],
                    [
                        'attribute' => 'coupon_money',
                        'headerOptions' => [
                            'width' => '80',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                    ],
                    [
                        'attribute' => 'coupon_remained',
                        'headerOptions' => [
                            'width' => '80',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                    ],
                    [
                        'attribute' => 'commission_money',
                        'headerOptions' => [
                            'width' => '80',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                    ],
                    [
                        'attribute' => 'sales_num',
                        'headerOptions' => [
                            'width' => '80',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                    ],
                    [
                        'attribute' => 'status',
                        'headerOptions' => [
                            'width' => '80',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                        'value' => function ($model) {
                            return Goods::BOOL_LABEL[$model->status];
                        }
                    ],
                    [
                        'attribute' => 'settop',
                        'headerOptions' => [
                            'width' => '80',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                        'value' => function ($model) {
                            return Goods::BOOL_LABEL[$model->settop];
                        }
                    ],
                    [
                        'attribute' => 'choice',
                        'headerOptions' => [
                            'width' => '80',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                        'value' => function ($model) {
                            return Goods::BOOL_LABEL[$model->choice];
                        }
                    ],
                    [
                        'attribute' => 'cid',
                        'headerOptions' => [
                            'width' => '100',
                            'class' => 'text-center',
                        ],
                        'contentOptions' => [
                            'class' => 'text-center',
                        ],
                        'value' => 'goodsCategory.title'
                    ],
                    [
                        'attribute' => 'end_time',
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

<script>
    <?php $this->beginBlock('footerJs') ?>
    function delSelect(ids) {
        $.ajax({
            url: '<?= Url::toRoute(['goods/del-select'])?>',
            method: 'POST',
            data: {ids: ids},
            success: function (res) {
                layer.closeAll();
                if (res.code === 0) {
                    layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                        window.location.href = location.href;
                    });
                }
                else {
                    layer.msg(res.msg, {icon: 2, time: 2000});
                }
            },
            error: function (xhr) {
                layer.closeAll();
                layer.msg('请求失败!', {icon: 2, time: 2000});
            }
        })
    }

    $("#select").click(function () {
        layer.confirm('确定删除?', {icon: 3, title: '提示'}, function (second) {
            var index = layer.load();
            var ids = $('#w1').yiiGridView('getSelectedRows');
            if (ids === null || ids === []) {
                layer.closeAll();
                layer.msg('请选择要删除的记录', {icon: 2, time: 2000});
            } else {
                delSelect(ids);
            }
        });
    });

    <?php $this->endBlock() ?>
</script>

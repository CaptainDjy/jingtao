<?php

use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use common\helpers\Utils;
use common\widgets\daterangepicker\DateRangePicker;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $dataProvider array
 * @var $searchArr array
 */

$this->title = '商品管理';
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
                <?= Html::beginForm(['goods/cate'], 'get', ['class' => 'form-horizontal']) ?>

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
                        <a href="<?= Url::toRoute(['goods/cate']) ?>">
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
                        'headerOptions' => ['width' => '100'],
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
                        'attribute' => 'sort',
                        'headerOptions' => ['width' => '100'],
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::textInput('sort', $model->sort, ['class' => 'form-control input-sm', 'data-id' => $model->id]);
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

<script>
    <?php $this->beginBlock('footerJs') ?>

    $('input[name=sort]').on('change', function () {
        var sort = $(this).val();
        var id = $(this).attr('data-id');
        if (isNaN(sort)) {
            alert('排序只能输入数字');
            $(this).val(0);
            return false;
        }
        var url = '<?= Url::toRoute('goods/cate-sort') ?>';
        $.post(url, {id: id, sort: sort}, function (res) {
            if (res.code === 0) {
                requirejs(['layer'], function () {
                    layer.msg('排序已更新！', {icon: 1, time: 2000},function () {
                        location.reload();
                    });
                });
            } else if (res.code === 1) {
                requirejs(['layer'], function () {
                    layer.alert(res.msg, {icon: 2, time: 2000})
                });
            } else {
                console.log(res);
            }
        }, 'json')
    });

    <?php $this->endBlock() ?>
</script>

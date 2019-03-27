<?php
/**
 * @var $this yii\web\View
 * @var $query yii\db\ActiveQuery
 * @var $searchArr array
 */

use backend\widgets\grid\MyGridView;
use common\models\Config;
use yii\bootstrap\Html;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;

?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <?= $this->render('_search-form', ['searchArr' => $searchArr]) ?>
            <?php
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => ['defaultOrder' => ['sort' => SORT_ASC]],
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);
            echo MyGridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'sort',
                        'enableSorting' => true,
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 80px'],
                        'contentOptions' => ['style' => 'padding:3px 8px'],
                        'value' => function ($model) {
                            return Html::textInput('sort', $model->sort, ['class' => 'form-control input-sm', 'data-id' => $model->id]);
                        }
                    ],
                    [
                        'attribute' => 'id',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a($model->name, Url::to(['config/update', 'id' => $model->id]), ['title' => '修改配置']);
                        }
                    ],
                    'title',
                    [
                        'attribute' => 'group',
                        'enableSorting' => true,
                        'format' => 'html',
                        'value' => function ($model) {
                            $groups = Config::getConfig('CONFIG_GROUP_LIST');
                            return @$groups[$model->group];
                        }
                    ],
                    [
                        'attribute' => 'type',
                        'enableSorting' => true,
                        'format' => 'html',
                        'value' => function ($model) {
                            $types = Config::getConfig('CONFIG_TYPE_LIST');
                            return $types[$model->type];
                        }
                    ],
                    [
                        'attribute' => 'status',
                        'enableSorting' => true,
                        'format' => 'html',
                        'value' => function ($model) {
                            return $model['status'] == 0 ? '<span class="badge bg-red">隐藏</span>' : '<span class="badge bg-green">显示</span>';
                        }
                    ],
                    [
                        'class' => 'backend\widgets\grid\MyActionColumn'
                    ],
                ]
            ]);
            ?>
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
        var url = '<?= Url::toRoute('config/update-sort') ?>';
        $.post(url, {id: id, sort: sort}, function (response) {
            if (response.code === '0') {
                requirejs(['layer'], function () {
                    layer.msg('排序已更新！', {icon: 1});
                });
            } else if (response.code === '1') {
                requirejs(['layer'], function () {
                    layer.alert(response.msg, {icon: 2})
                });
            } else {
                console.log(response);
            }
        }, 'json')
    });

    <?php $this->endBlock() ?>
</script>

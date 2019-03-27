<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/13 19:05
 */

/**
 * @var $this yii\web\View
 * @var $model yii\db\Query
 */

use backend\widgets\grid\MyGridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">

            <div class="box-header clearfix no-border">
                <a href="<?= Url::toRoute(['auth-manager/role-edit']) ?>" data-toggle='modal' data-target='#ajaxModal'
                   class="btn btn-primary pull-left">
                    <i class="fa fa-plus"></i> 添加
                </a>
            </div>

            <?php
            $dataProvider = new ActiveDataProvider([
                'query' => $model,
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);
            echo MyGridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'name',
                        'enableSorting' => true,
                        'label' => '角色'
                    ],
                    [
                        'attribute' => 'created_at',
                        'enableSorting' => true,
                        'format' => ['date', 'php:Y-m-d']
                    ],

                    [
                        'class' => 'backend\widgets\grid\MyActionColumn',
                        'template' => '{role-accredit} {role-edit} {role-del}',
                        'contentOptions' => ['style' => 'padding:3px 8px'],
                        'buttons' => [
                            'role-accredit' => function ($url, $model, $key) {
                                return Html::a('授权', Url::toRoute(['auth-manager/role-accredit', 'name' => $model->name]), ['class' => 'btn btn-sm btn-primary']);
                            },
                            'role-edit' => function ($url, $model, $key) {
                                return Html::a('编辑', Url::toRoute(['auth-manager/role-edit', 'name' => $model->name]), ['class' => 'btn btn-sm btn-success', 'data-toggle' => 'modal', 'data-target' => '#ajaxModal']);
                            },
                            'role-del' => function ($url, $model, $key) {
                                return Html::a('删除', Url::toRoute(['auth-manager/role-del', 'name' => $model->name]), ['class' => 'btn btn-sm btn-warning', 'onclick' => "return confirm('确认删除吗？');return false;"]);
                            }
                        ]
                    ],
                ]
            ]);
            ?>
        </div>
    </div>
</div>

<!--ajax模拟框加载-->
<div class="modal fade" id="ajaxModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <img src="<?= Yii::getAlias('@static/img/loading.gif') ?>" alt="" class="loading">
                <span>&nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
</div>


<script>
    <?php $this->beginBlock('footerJs') ?>
    $('#ajaxModal').on('hide.bs.modal', function () {
        $(this).removeData("bs.modal");
    });
    <?php $this->endBlock() ?>
</script>

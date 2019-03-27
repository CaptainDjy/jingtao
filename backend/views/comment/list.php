<?php
/**
 * @var $this yii\web\View
 * @var $list yii\db\ActiveQuery
 * @var $keywords string
 */

use backend\widgets\grid\MyGridView;
use yii\data\ActiveDataProvider;

?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <?php
            $dataProvider = new ActiveDataProvider([
                'query' => $list,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]);
            $arr = $list->all();
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
                    [
                        'attribute' => 'gid',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'content',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'html',
                        'value' => function ($model) {
                            return $model['status'] == 0 ? '<span class="badge bg-red">隐藏</span>' : '<span class="badge bg-green">显示</span>';
                        },
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                        'enableSorting' => true,
                        'label' => '创建时间'
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

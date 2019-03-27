<?php
/**
 * @author pine
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * createtime: 2018/05/26 17:00
 */

/**
 * @var $this yii\web\View
 * @var $query yii\db\ActiveQuery
 * @var $searchArr array
 * @var $type string
 */


use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use common\models\AdvertSpace;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = '推广位列表';
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
                <?= Html::beginForm(['advert-space/index'], 'get', ['class' => 'form-horizontal']) ?>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">关键词</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= Html::textInput('keywords', $searchArr['keywords'], ['class' => 'form-control', 'placeholder' => "请输入用户UID"]); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= Html::submitButton('搜索', ['class' => 'btn btn-success']); ?>
                        <a href="<?= Url::toRoute(['advert-space/index']) ?>">
                            <div class="btn btn-default"><i class="glyphicon glyphicon-leaf"></i> 清除筛选</div>
                        </a>

                        <a href="<?= Url::current(['type' => '1']) ?>" class="btn <?= $type == 1 ? 'btn-success' : 'btn-default' ?>">
                            淘宝推广位 <span class="badge label-success"><?= (clone $query)->where(['type' => 1,'status'=>1])->count() ?></span>
                        </a>
                        <a href="<?= Url::current(['type' => '2']) ?>" class="btn <?= $type == 2 ? 'btn-success' : 'btn-default' ?>">
                            京东推广位 <span class="badge label-success"><?= (clone $query)->where(['type' => 2,'status'=>1])->count() ?></span>
                        </a>
                        <a href="<?= Url::current(['type' => '3']) ?>" class="btn <?= $type == 3 ? 'btn-success' : 'btn-default' ?>">
                            拼多多推广位 <span class="badge label-success"><?= (clone $query)->where(['type' => 3,'status'=>1])->count() ?></span>
                        </a>
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
                    'pageSize' => 20,
                ],
                'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            ]);
            $arr = $query->all();
            echo MyGridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'enableSorting' => true,
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'type',
                        'value' => function ($model) {
                            if (empty(AdvertSpace::TYPE[$model->type])) {
                                return '未知';
                            } else {
                                return AdvertSpace::TYPE[$model->type];
                            }
                        }
                    ],
                    [
                        'attribute' => 'pid',
                    ],
                    [
                        'attribute' => 'uid',
                    ],
                    [
                        'attribute' => 'title',
                    ],
                    [
                        'attribute' => 'status',
                        'label' => '状态',
                        'format' => 'raw',
                        'value' => function ($model) {
                            if ($model->status == 1) {
                                $str = '<span class="badge bg-green">已启用</span>';
                            } else {
                                $str = '<span class="badge bg-yellow">未启用</span>';
                            }
                            return $str;
                        }
                    ],
                    [
                        'label' => '注册时间',
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                    ],
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
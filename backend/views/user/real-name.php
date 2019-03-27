<?php
/** @var string $keywords
 * @var string $list
 */


use backend\widgets\grid\MyActionColumn;
use common\helpers\Utils;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="<?= Url::to(['/withdraw/list']) ?>">订单列表</a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="box box-success">
            <div class="box-header with-border"></div>
            <div class="box-body">
                <form class="form-horizontal" method="get">
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">关键词</label>
                        <div class="col-xs-12 col-sm-5">
                            <input type="hidden" name="r" value="user/real-name">
                            <input type="text" class="form-control" name="keywords" value="<?= $keywords ?>"
                                   placeholder="请输入UID搜索">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                        <div class="col-xs-12 col-sm-5">
                            <button type="submit" class="btn btn-info">搜索</button>
                            <!--                            < ?= Html::submitButton('导出会员列表', ['class' => 'btn btn-primary pull-right','name'=>'op', 'value'=>'export']); ?>-->
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="tab-pane active">
            <?php
            $dataProvider = new \yii\data\ActiveDataProvider([
                'query' => $list,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]);
            echo \backend\widgets\grid\MyGridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'report_uid',
                        'enableSorting' => true,
                    ],
                    [
                        'label' => '报单中心姓名',
                        'enableSorting' => true,
                        'value' => function ($model) {
                            $user = \common\models\User::findOne($model->report_uid);
                            return $user['nickname'];
                        }
                    ],
                    [
                        'attribute' => 'uid',
                        'enableSorting' => true,
                    ],
                    [
                        'label' => '报单人姓名',
                        'enableSorting' => true,
                        'value' => function ($model) {
                            $user = \common\models\User::findOne($model->uid);
                            return $user['nickname'];
                        }
                    ],
                    [
                        'attribute' => 'price',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'thumb',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a(Html::img(Utils::toMedia($model->thumb),
                                ['class' => 'img-rectangle',
                                    'height' => 50, 'width' => 100]
                            ), ['/article/big-pic', 'id' => $model->thumb], ['title' => '放大图片', 'data-toggle' => 'modal', 'data-target' => '#ajaxModal']) ?: '';
                        },
                    ],
                    [
                        'attribute' => 'card_front',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a(Html::img(Utils::toMedia($model->card_front),
                                ['class' => 'img-rectangle',
                                    'height' => 50, 'width' => 100]
                            ), ['/article/big-pic', 'id' => $model->card_front], ['title' => '放大图片', 'data-toggle' => 'modal', 'data-target' => '#ajaxModal']) ?: '';
                        },
                    ],
                    [
                        'attribute' => 'card_negative',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a(Html::img(Utils::toMedia($model->card_negative),
                                ['class' => 'img-rectangle',
                                    'height' => 50, 'width' => 100]
                            ), ['/article/big-pic', 'id' => $model->card_negative], ['title' => '放大图片', 'data-toggle' => 'modal', 'data-target' => '#ajaxModal']) ?: '';
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'enableSorting' => true,
                        'value' => function ($model) {
                            if ($model->status == 0) {
                                $str = '未审核';
                            } elseif ($model->status == 1) {
                                $str = '审核中';
                            } elseif ($model->status == 2) {
                                $str = '已审核';
                            } elseif ($model->status == 3) {
                                $str = '驳回';
                            } else {
                                $str = '未知';
                            }
                            return $str;
                        },
                    ],
                    [
                        'attribute' => 'remarks',
                    ],
                    [
                        'label' => '申请时间',
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                        'enableSorting' => true,
                    ],
                    [
                        'class' => MyActionColumn::class,
                        'template' => '{role-del}&nbsp;&nbsp;{delete}',
                        'buttons' => [
                            'role-del' => function ($url, $model, $key) {
                                return ($model->status == 1) ? Html::a('同意', Url::toRoute(['user/real-update', 'id' => $model->id]), ['class' => 'btn btn-sm btn-warning', 'onclick' => "return confirm('确认同意该订单吗？');return false;"]) : '';
                            },
                            'delete' => function ($url, $model, $key) {
                                return ($model->status == 1) ? Html::a('拒绝', Url::toRoute(['user/real-reject', 'id' => $model->id]), ['class' => 'btn btn-sm btn-warning', 'title' => '驳回原因', 'data-toggle' => 'modal', 'data-target' => '#ajaxModal']) : '';
                            }
                        ],
                    ]
                ]
            ]);
            ?>
        </div>
    </div>
</div>

<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/22 13:55
 */

/**
 * @var $this yii\web\View
 * @var $form yii\bootstrap\ActiveForm
 */

use backend\models\SystemUser;
use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = '系统用户管理';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
<!--            <div class="box-header clearfix no-border">-->
<!--                <a href="--><?//= Url::toRoute(['system-user/user-edit']) ?><!--" data-toggle='modal' data-target='#ajaxModal'-->
<!--                   class="btn btn-primary pull-left">-->
<!--                    <i class="fa fa-plus"></i> -->
<!--                </a>-->
<!--            </div>-->
            <?php
            $dataProvider = new ActiveDataProvider([
                'query' => SystemUser::findStatus(),
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]);
            echo MyGridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'username',
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'status',
                        'enableSorting' => true,
                        'format' => 'html',
                        'value' => function($model){
                            return $model->status ==10?'<span class="badge bg-green">正常</span>':'<span class="badge bg-red">删除</span>';
                        }
                    ],
                    [
                        'attribute' => 'role_id',
                        'label' => '角色权限',
                        'enableSorting' => true,
                        'format' => 'html',
                        'value' => function ($model) {
                            $str = \backend\models\SystemAuthItem::roleStatus($model->role_id) ?: '超级管理员';
                            return '<span class="badge badge-info" style="background-color:#23c6c8">' . $str . '</span>';
                        }
                    ],
                    [
                        'class' => MyActionColumn::class,
                        'template' => '{user-edit}&nbsp;&nbsp;{delete}',
                        'buttons' => [
//                            'update' => function ($url, $model) {
//                                return SystemUser::isSystemAdmin($model->username) ? '' : Html::a('授权角色', $url, ['class' => 'btn btn-sm btn-primary', 'title' => '授权角色', 'data-toggle' => 'modal', 'data-target' => '#ajaxModal']);
//                            },
                            'user-edit' => function ($url, $model) {
                                return Html::a('账号管理', $url, ['class' => 'btn btn-sm btn-success', 'title' => '账号管理', 'data-toggle' => 'modal', 'data-target' => '#ajaxModal']);
                            },

                            'delete' => function ($url, $model) {
                                return SystemUser::isSystemAdmin($model->username) ? '' : Html::a('删除', $url, ['class' => 'btn btn-sm btn-warning', 'onclick' => "return confirm('确认删除吗？');return false;"]);
                            }
                        ],
                    ],
                ]
            ]);
            ?>
        </div>
    </div>
</div>

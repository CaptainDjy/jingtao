<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

use backend\widgets\grid\MyActionColumn;
use backend\widgets\grid\MyGridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $query \common\models\RobotHaoquan
 */
$this->title = '好券清单采集';
$goodsCategoryMap = \common\models\GoodsCategory::map();
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <button type="button" class="btn btn-primary" onclick="runRobots()">一键采集</button>
            <?php
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 20,
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
                        'attribute' => 'title',
                    ],
                    [
                        'attribute' => 'from_cid',
                    ],
                    [
                        'label' => '采集类名',
                        'value' => function ($model) {
                            return \common\models\RobotHaoquan::FROM_CATEGORY[$model->from_cid];
                        }
                    ],
                    [
                        'attribute' => 'to_cid',
                    ],
                    [
                        'label' => '入库类名',
                        'value' => function ($model) use ($goodsCategoryMap) {
                            return $goodsCategoryMap[$model->to_cid];
                        }
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                        'enableSorting' => true,
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => ['date', 'php:Y-m-d H:i:s'],
                        'enableSorting' => true,
                    ],
                    [
                        'class' => MyActionColumn::class,
                        'headerOptions' => ['width' => '160'],
                        'template' => '{update}&nbsp;&nbsp;{delete}'
                    ],
                ]
            ]);
            ?>
        </div>
    </div>
</div>

<script>
    function runRobots() {
        var run = function (pageNum, num) {
            $.post('<?= Url::to(['robot-haoquan/run'])?>', {pageNum: pageNum, num: num}, function (response) {
                if (response.code === 0) {
                    $('.layui-layer-content').text(response.msg);
                    pageNum = response.data.pageNum + 1;
                    num = response.data.num;
                    console.log(pageNum, num);
                    run(pageNum, num);
                } else {
                    $('.layui-layer-content').text(response.msg);
                }
            }, 'json')
        };

        requirejs(['layer'], function () {
            layer.prompt({
                title: '输入起始页码，并确认开始',
                formType: 0,
                value: 1,
                btn: ['确定', '取消']
            }, function (value, index, elem) {
                layer.open({
                    type: 0,
                    title: '请勿关闭窗口',
                    content: '开始采集，正在准备数据，请稍候！',
                    btn: false,
                    area: ['300px', '150px'],
                    cancel: function (index, layero) {
                        layer.close(index);
                        location.reload();
                    }
                });
                layer.close(index);
                run(value, 0)
            }, function () {
                layer.msg('采集已取消', {icon: 0});
            });
        });
    }
</script>



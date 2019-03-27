<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/26 16:19
 */

/**
 * @var \backend\models\DistributionConfig $config
 * @var string $name
 * @var array $config
 */

use yii\helpers\Html;

$this->title = '参数设置';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <?= Html::beginForm(['finance/aas'], 'post', ['class' => 'form-horizontal']); ?>
            <div class="box box-success">
                <div class="box-header with-border">

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">平台扣留</label>
                    <div class="form-group">
                        <div class="col-xs-3">
                            <div class="input-group">
                                <span class="input-group-addon">平台扣留百分比</span>
                                <input name="rzh" value="<?= $detain ?>" class="form-control"
                                       placeholder="请输入整数">
                                <span class="input-group-addon">%</span>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">上三级返利</label>
                    <div class="col-xs-6 col-sm-4 col-md-4 col-lg-4">
                        <div class="input-group">
                            <span class="input-group-addon">一级</span>
                            <input name="stair" value="<?=$stair?>" class="form-control"
                                   placeholder="请输入整数">
                            <span class="input-group-addon">%</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">二级</span>
                            <input name="second" value="<?=$second?>" class="form-control"
                                   placeholder="请输入整数">
                            <span class="input-group-addon">%</span>
                        </div>
<!--                        <div class="input-group">-->
<!--                            <span class="input-group-addon">三级</span>-->
<!--                            <input name="threelevel" value="--><?//=$threelevel?><!--" class="form-control"-->
<!--                                   placeholder="请输入整数">-->
<!--                            <span class="input-group-addon">%</span>-->
<!--                        </div>-->
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">自购佣金比例</label>
                    <div class="col-xs-6 col-sm-4 col-md-4 col-lg-4">
                        <div class="input-group">
                            <span class="input-group-addon">会员</span>
                            <input name="zghy" value="<?= $zghy ?>" class="form-control"
                                   placeholder="请输入整数">
                            <span class="input-group-addon">%</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">代理</span>
                            <input name="zgdl" value="<?= $zgdl ?>" class="form-control"
                                   placeholder="请输入整数">
                            <span class="input-group-addon">%</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon">总代</span>
                            <input name="zgzd" value="<?=$zgzd ?>" class="form-control"
                                   placeholder="请输入整数">
                            <span class="input-group-addon">%</span>
                        </div>

                    </div>
                </div>

            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                <div class="col-xs-12 col-sm-5">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
            <?= Html::endForm() ?>
        </div>

    </div>
</div>


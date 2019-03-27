<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/29
 * Time: 11:12
 */
use yii\helpers\Html;
use yii\helpers\Url;

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
                <?= Html::beginForm(['user/lv'], 'post', ['class' => 'form-horizontal']) ?>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">高佣</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= Html::textInput('sole', '', ['class' => 'form-control', 'placeholder' => "人数为整数"]); ?>
                        <div class="help-block">输入享高佣需要推荐的人数</div>
                    </div>
                </div>
                <br>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">要求</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        &nbsp;&nbsp;高佣需要推荐<span class="badge bg-green"><?=
                            $sole ?></span>人
                    </div>
                </div>



                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                       <?= Html::submitButton('提交', ['class' => 'btn btn-success']); ?>
                        <a href="<?= Url::toRoute(['user/lv']) ?>">
                            <div class="btn btn-default"><i class="glyphicon glyphicon-leaf"></i> 重置</div>
                        </a>
                    </div>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>


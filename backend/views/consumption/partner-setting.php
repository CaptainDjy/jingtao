<?php
use yii\bootstrap\Html;

?>

<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <?= Html::beginForm('', 'post', ['class' => 'form-horizontal']); ?>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">认证需要</label>
                <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                    <div class="input-group">
                        <input name="config[prove][zTimes]" value="<?= @$config['prove']['zTimes'] ?: 1 ?>"
                               class="form-control" placeholder="请输入整数X">
                        <span class="input-group-addon">的整数倍自生碳汇， 最少</span>
                        <input name="config[prove][zMin]" value="<?= @$config['prove']['zMin'] ?: 1 ?>"
                               class="form-control" placeholder="请输入整数Y个自生碳汇">
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">生效天数</span>
                        <input name="config[prove][effectiveDay]" value="<?= @$config['prove']['effectiveDay'] ?: 1 ?>"
                               class="form-control" placeholder="请输入整数">
                    </div>
                    <div class="help-block">认证碳汇需要自生碳汇数量是X的整数倍,最少需要Y个</div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">认证比例</label>
                <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                    <div class="input-group">
                        <input name="config[prove][sRate]" value="<?= @$config['prove']['sRate'] ?: 1 ?>"
                               class="form-control" placeholder="请输入整数">
                        <span class="input-group-addon">%手续费</span>
                        <input name="config[prove][tRate]" value="<?= @$config['prove']['tRate'] ?: 1 ?>"
                               class="form-control" placeholder="请输入整数">
                        <span class="input-group-addon">%碳汇</span>
                        <input name="config[prove][gRate]" value="<?= @$config['prove']['gRate'] ?: 1 ?>"
                               class="form-control" placeholder="请输入整数">
                        <span class="input-group-addon">%金碳汇</span>
                    </div>
                    <div class="help-block">请输入整数，不需要百分号</div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">C贝提现</label>
                <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                    <div class="input-group">
                        <span class="input-group-addon">1个C贝可换取</span>
                        <input name="config[withdraw][cRate]" value="<?= @$config['withdraw']['cRate'] ?: 1 ?>"
                               class="form-control" placeholder="请输入整数">
                        <span class="input-group-addon">积分</span>
                    </div>
                    <div class="help-block">积分只能为整数</div>
                    <div class="input-group">
                        <input name="config[withdraw][cTimes]" value="<?= @$config['withdraw']['cTimes'] ?: 1 ?>"
                               class="form-control" placeholder="请输入整数X">
                        <span class="input-group-addon">的整数倍C贝， 最少</span>
                        <input name="config[withdraw][cMin]" value="<?= @$config['withdraw']['cMin'] ?: 1 ?>"
                               class="form-control" placeholder="请输入整数Y个C贝">
                    </div>
                    <div class="help-block">提现需要C贝数量是X的整数倍,最少需要Y个</div>
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


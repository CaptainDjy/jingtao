<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/29 11:32
 */


/**
 * @var array $config
 */
$integral = \common\models\Config::getConfig('LIBERATED_INTEGRAL_AGREE') ?: [];
?>

<div class="box box-success">
    <div class="box-header with-border">
        商铺设置
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">千返设置</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <div class="input-group">
                    <span class="input-group-addon">按消费额的</span>
                    <input name="config[isMoney]" value="<?= @$config['isMoney'] ?>" class="form-control"
                           placeholder="请输入整数">
                    <span class="input-group-addon">%千返</span>
                </div>
                <div class="help-block">千返设置，设置为0则不进行千返</div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">提现手续费</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <div class="input-group">
                    <span class="input-group-addon">按消费额的</span>
                    <input name="config[withdrfee]" value="<?= @$config['withdrfee'] ?>" class="form-control"
                           placeholder="请输入整数">
                    <span class="input-group-addon">%</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">兑换手续费</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <div class="input-group">
                    <span class="input-group-addon">按消费额的</span>
                    <input name="config[exchangefee]" value="<?= @$config['exchangefee'] ?>" class="form-control"
                           placeholder="请输入整数">
                    <span class="input-group-addon">%</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">兑换志愿者</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <div class="input-group">
                    <span class="input-group-addon">按消费额的</span>
                    <input name="config[exchangevol]" value="<?= @$config['exchangevol'] ?>" class="form-control"
                           placeholder="请输入整数">
                    <span class="input-group-addon">%</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">商铺设置</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <div class="input-group">
                    <span class="input-group-addon">商铺只能提取</span>
                    <input name="config[money]" value="<?= @$config['money'] ?>" class="form-control"
                           placeholder="请输入整数">
                    <span class="input-group-addon">%消费额，剩余</span>
                    <input value="<?= 100 - @$config['money'] ?>" class="form-control" placeholder="请输入整数" disabled>
                    <span class="input-group-addon">%千返</span>

                </div>
                <div class="help-block">请输入整数，不需要百分号</div>
            </div>
        </div>

    </div>
</div>

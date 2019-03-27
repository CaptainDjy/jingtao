<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/29 11:32
 */

?>

<div class="box box-success">
    <div class="box-header with-border">
        提现
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
                    <input name="config[platform]" value="<?= @$config['platform'] ?>" class="form-control"
                           placeholder="请输入整数">
                    <span class="input-group-addon">%</span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">自购佣金比例</label>
        <div class="col-xs-6 col-sm-4 col-md-4 col-lg-4">
            <div class="input-group">
                <span class="input-group-addon">粉丝</span>
                <input name="config[selfcomm][0]" value="<?= @$config['selfcomm'][0] ?>" class="form-control"
                       placeholder="请输入整数">
                <span class="input-group-addon">%</span>
            </div>
            <div class="input-group">
                <span class="input-group-addon">一级</span>
                <input name="config[selfcomm][1]" value="<?= @$config['selfcomm'][1] ?>" class="form-control"
                       placeholder="请输入整数">
                <span class="input-group-addon">%</span>
            </div>
            <div class="input-group">
                <span class="input-group-addon">二级</span>
                <input name="config[selfcomm][2]" value="<?= @$config['selfcomm'][2] ?>" class="form-control"
                       placeholder="请输入整数">
                <span class="input-group-addon">%</span>
            </div>
            <div class="input-group">
                <span class="input-group-addon">三级</span>
                <input name="config[selfcomm][3]" value="<?= @$config['selfcomm'][3] ?>" class="form-control"
                       placeholder="请输入整数">
                <span class="input-group-addon">%</span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">上三级返利</label>
        <div class="col-xs-6 col-sm-4 col-md-4 col-lg-4">
            <div class="input-group">
                <span class="input-group-addon">一级</span>
                <input name="config[consump][1]" value="<?= @$config['consump'][1] ?>" class="form-control"
                       placeholder="请输入整数">
                <span class="input-group-addon">%</span>
            </div>
            <div class="input-group">
                <span class="input-group-addon">二级</span>
                <input name="config[consump][2]" value="<?= @$config['consump'][2] ?>" class="form-control"
                       placeholder="请输入整数">
                <span class="input-group-addon">%</span>
            </div>
            <div class="input-group">
                <span class="input-group-addon">三级</span>
                <input name="config[consump][3]" value="<?= @$config['consump'][3] ?>" class="form-control"
                       placeholder="请输入整数">
                <span class="input-group-addon">%</span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">团队奖返利</label>
        <div class="col-xs-6 col-sm-4 col-md-4 col-lg-4">
            <div class="input-group">
                <span class="input-group-addon">一级</span>
                <input name="config[team][1]" value="<?= @$config['team'][1] ?>" class="form-control"
                       placeholder="请输入整数">
                <span class="input-group-addon">%</span>
            </div>
            <div class="input-group">
                <span class="input-group-addon">二级</span>
                <input name="config[team][2]" value="<?= @$config['team'][2] ?>" class="form-control"
                       placeholder="请输入整数">
                <span class="input-group-addon">%</span>
            </div>
            <div class="input-group">
                <span class="input-group-addon">三级</span>
                <input name="config[team][3]" value="<?= @$config['team'][3] ?>" class="form-control"
                       placeholder="请输入整数">
                <span class="input-group-addon">%</span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">一级晋升</label>
        <div class="form-group">
            <div class="col-xs-6">
                <div class="input-group">
                    <span class="input-group-addon">直推粉丝总数</span>
                    <input name="config[rechargeback][1][directNum]"
                           value="<?= @$config['rechargeback'][1]['directNum'] ?>" class="form-control"
                           placeholder="请输入升级条件" style="min-width: 200px;max-width: 500px">
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">二级晋升</label>
        <div class="form-group">
            <div class="col-xs-6">
                <div class="input-group">
                    <span class="input-group-addon">直推付费总数</span>
                    <input name="config[rechargeback][2][directPayment]"
                           value="<?= @$config['rechargeback'][2]['directPayment'] ?>" class="form-control"
                           placeholder="请输入升级条件" style="min-width: 200px;max-width: 500px">
                    <span class="input-group-addon">直推总人数</span>
                    <input name="config[rechargeback][2][directNum]"
                           value="<?= @$config['rechargeback'][2]['directNum'] ?>" class="form-control"
                           placeholder="请输入升级条件" style="min-width: 200px;max-width: 500px">
                </div>
                <div class="input-group">
                    <span class="input-group-addon">团队付费总数</span>
                    <input name="config[rechargeback][2][teamPayment]"
                           value="<?= @$config['rechargeback'][2]['teamPayment'] ?>" class="form-control"
                           placeholder="请输入升级条件" style="min-width: 200px;max-width: 500px">
                    <span class="input-group-addon">团队总人数</span>
                    <input name="config[rechargeback][2][teamNum]"
                           value="<?= @$config['rechargeback'][2]['teamNum'] ?>" class="form-control"
                           placeholder="请输入升级条件" style="min-width: 200px;max-width: 500px">
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">三级晋升</label>
        <div class="form-group">
            <div class="col-xs-6">
                <div class="input-group">
                    <span class="input-group-addon">直推付费总数</span>
                    <input name="config[rechargeback][3][directPayment]"
                           value="<?= @$config['rechargeback'][3]['directPayment'] ?>" class="form-control"
                           placeholder="请输入需充值金额" style="min-width: 200px;max-width: 500px">
                    <span class="input-group-addon">直推总人数</span>
                    <input name="config[rechargeback][3][directNum]"
                           value="<?= @$config['rechargeback'][3]['directNum'] ?>" class="form-control"
                           placeholder="请输入提成比例" style="min-width: 200px;max-width: 500px">
                </div>
                <div class="input-group">
                    <span class="input-group-addon">团队付费总数</span>
                    <input name="config[rechargeback][3][teamPayment]"
                           value="<?= @$config['rechargeback'][3]['teamPayment'] ?>" class="form-control"
                           placeholder="请输入需充值金额" style="min-width: 200px;max-width: 500px">
                    <span class="input-group-addon">团队总人数</span>
                    <input name="config[rechargeback][3][teamNum]"
                           value="<?= @$config['rechargeback'][3]['teamNum'] ?>" class="form-control"
                           placeholder="请输入提成比例" style="min-width: 200px;max-width: 500px">
                </div>
            </div>
        </div>
    </div>
</div>

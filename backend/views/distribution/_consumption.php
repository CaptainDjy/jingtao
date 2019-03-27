<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/29 11:32
 */

/**
 * @var array $config
 */
$name = \common\models\Config::getConfig('CONSUMPTION_NAME');
$name_level = \common\models\Config::getConfig('CONSUMPTION_NAME_LEVEL');
$level = \common\models\Config::getConfig('CONSUMPTION_LEVEL');
?>

<div class="box box-success">
    <div class="box-header with-border">
        消费级别设置
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">是否千返</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <label class="radio-inline">
                    <input type="radio" name="config[isMoney]"
                           value="1" <?= (empty($config['isMoney']) || $config['isMoney'] == '1') ? 'checked' : '' ?>>
                    开启
                </label>
                <label class="radio-inline">
                    <input type="radio" name="config[isMoney]"
                           value="0" <?= @$config['isMoney'] == '0' ? 'checked' : '' ?>> 关闭
                </label>
                <div class="help-block">是否开启千返模式</div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">消费返比</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <div class="input-group">
                    <span class="input-group-addon">按消费额的</span>
                    <input name="config[returnConsum]" value="<?= @$config['returnConsum'] ?>" class="form-control"
                           placeholder="请输入整数">
                    <span class="input-group-addon">%</span>
                </div>
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
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">返现限制</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <div class="input-group">
                    <span class="input-group-addon">累计消费满</span>
                    <input name="config[impose]" value="<?= @$config['impose'] ?>" class="form-control"
                           placeholder="请输入整数">
                    <span class="input-group-addon">开始千返</span>
                </div>
                <div class="help-block">请输入金额，不需要小数点</div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">消费总提成</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <div class="input-group">
                    <span class="input-group-addon">消费总提成</span>
                    <input name="config[consumptionRatio]" value="<?= @$config['consumptionRatio'] ?>"
                           class="form-control" placeholder="请输入提成比例">
                    <span class="input-group-addon">%提成比例</span>
                </div>
                <div class="help-block">请输入整数，不需要百分号</div>
            </div>
        </div>


        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">会员充值返比</label>
            <div class="form-group">
                <div class="col-xs-6">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div class="input-group">
                            <span class="input-group-addon">充值金额</span>
                            <input name="config[rechargeback][<?= $i ?>][money]"
                                   value="<?= @$config['rechargeback'][$i]['money'] ?>" class="form-control"
                                   placeholder="请输入需充值金额" style="min-width: 200px;max-width: 500px">
                            <span class="input-group-addon">元,充值比例</span>
                            <input name="config[rechargeback][<?= $i ?>][num]"
                                   value="<?= @$config['rechargeback'][$i]['num'] ?>" class="form-control"
                                   placeholder="请输入提成比例" style="min-width: 200px;max-width: 500px">
                            <span class="input-group-addon">% </span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">消费级别</label>
            <div class="form-group">
                <div class="col-xs-6">
                    <?php for ($i = 1; $i <= 32; $i++): ?>
                        <div class="input-group">
                            <span class="input-group-addon">等级<?= $i ?>，需要</span>
                            <input name="config[level][<?= $i ?>][num]" value="<?= @$config['level'][$i]['num'] ?>"
                                   class="form-control" placeholder="请输入积分数量" style="min-width: 200px;max-width: 500px">
                            <span class="input-group-addon">的累计积分</span>
                            <input name="config[level][<?= $i ?>][ratio]" value="<?= @$config['level'][$i]['ratio'] ?>"
                                   class="form-control" placeholder="请输入提成比例" style="min-width: 200px;max-width: 500px">
                            <span class="input-group-addon">%提成比例</span>
                        </div>
                    <?php endfor; ?>
                    <div class="help-block">数量要求整数，提成比例是百分多少</div>
                </div>
            </div>
        </div>
    </div>
</div>

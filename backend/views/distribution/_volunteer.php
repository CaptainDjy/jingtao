<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/29 11:32
 */

/**
 * @var array $config
 */
$name = \common\models\Config::getConfig('VOLUNTEER_APPLY_NAME');
$level = \common\models\Config::getConfig('VOLUNTEER_APPLY_LEVEL');
?>

<div class="box box-success">
    <div class="box-header with-border">
        志愿者设置
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
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">充值比例</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <div class="input-group">
                    <span class="input-group-addon">按消费额的</span>
                    <input name="config[rechargecolumn]" value="<?= @$config['rechargecolumn'] ?>" class="form-control"
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
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">赠送积分比例</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <div class="input-group">
                    <span class="input-group-addon">1人民币可赠送</span>
                    <input name="config[consumRatio]" value="<?= @$config['consumRatio'] ?>" class="form-control"
                           placeholder="请输入整数">
                    <span class="input-group-addon">积分</span>
                </div>
                <div class="help-block">积分设置可以为小数</div>
            </div>
        </div>


        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">消费额提成</label>
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
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">推广店铺</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <div class="input-group">
                    <span class="input-group-addon">推广店铺立返</span>
                    <input name="config[generalize]" value="<?= @$config['generalize'] ?>" class="form-control"
                           placeholder="请输入提成比例">
                    <span class="input-group-addon">元</span>
                </div>
                <div class="help-block">请输入金额，金额为整数</div>
            </div>
        </div>


        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">志愿者设置</label>
            <div class="form-group">
                <div class="col-xs-6">
                    <?php foreach ($level as $k => $v): ?>
                        <div class="input-group">
                            <span class="input-group-addon"><?= $name[$k] ?></span>

                            <span class="input-group-addon">志愿者提成(%)</span>
                            <input name="config[level][<?= $k ?>][shop]" value="<?= @$config['level'][$k]['shop'] ?>"
                                   class="form-control" placeholder="请输入提成">


                            <span class="input-group-addon">消费等级</span>
                            <input name="config[level][<?= $k ?>][consume]"
                                   value="<?= @$config['level'][$k]['consume'] ?>" class="form-control"
                                   placeholder="请输入提成">

                            <span class="input-group-addon">所需金额(元)</span>
                            <input name="config[level][<?= $k ?>][money]" value="<?= @$config['level'][$k]['money'] ?>"
                                   class="form-control" placeholder="请输入金额">


                        </div>
                    <?php endforeach; ?>
                    <div class="help-block">数量要求整数，提成比例是百分多少</div>
                </div>
            </div>
        </div>
    </div>
</div>

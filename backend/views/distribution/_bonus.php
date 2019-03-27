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
        代理分红设置
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">人数限制</label>
            <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
                <label class="radio-inline">
                    <input type="radio" name="config[switch]"
                           value="1" <?= (empty($config['switch']) || $config['switch'] == '1') ? 'checked' : '' ?>> 开启
                </label>
                <label class="radio-inline">
                    <input type="radio" name="config[switch]"
                           value="0" <?= @$config['switch'] == '0' ? 'checked' : '' ?>> 关闭
                </label>
                <div class="help-block">人数限制开启则设置代理人数</div>
            </div>

        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">代理设置</label>
            <div class="form-group">
                <div class="col-xs-6">
                    <?php foreach ($level as $k => $v): ?>
                        <div class="input-group">
                            <span
                                class="input-group-addon"><?= $name[substr($k, 0, 1)] . $name_level[substr($k, 1, 1)] ?>
                                拿取</span>
                            <input name="config[prove][sRate]" value="<?= @$config['consumption'] ?>"
                                   class="form-control" placeholder="请输入提成">
                            <span class="input-group-addon">%提成比例，所需金额</span>
                            <input name="config[prove][sRate]" value="<?= @$config['consumption'] ?>"
                                   class="form-control" placeholder="请输入金额">
                            <span class="input-group-addon">元，代理人数</span>
                            <input name="config[prove][sRate]" value="<?= @$config['consumption'] ?>"
                                   class="form-control" placeholder="请输入人数">
                            <span class="input-group-addon">人</span>
                        </div>
                    <?php endforeach; ?>
                    <div class="help-block">数量要求整数，提成比例是百分多少</div>
                </div>
            </div>
        </div>
    </div>
</div>

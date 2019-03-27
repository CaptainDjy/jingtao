<?php

use yii\helpers\Html;

$this->title = '商品管理';

?>

<div class="nav-tabs-custom">
    <div class="tab-content">
        <div class="tab-pane active">
            <?= Html::beginForm('', 'post', ['class' => 'form-horizontal']); ?>

            <div class="box box-success">
                <div class="box-header with-border">
                    排序
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">排序</label>
                    <div class="form-group">
                        <div class="col-xs-3">
                            <div class="input-group">
                                <?= Html::radioList('config[order]', $config['order'], ['1' => '升序', '2' => '降序', ], ['class' => 'form-control']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">销量</label>
                    <div class="form-group">
                        <div class="col-xs-2">
                            <div class="input-group">
                                <input name="config[min_volume]" value="<?= @$config['min_volume'] ?>" class="form-control" placeholder="最小销量">
                                <span class="input-group-addon">--</span>
                                <input name="config[max_volume]" value="<?= @$config['max_volume'] ?>" class="form-control" placeholder="最大销量">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">佣金比例</label>
                    <div class="form-group">
                        <div class="col-xs-2">
                            <div class="input-group">
                                <input name="config[min_rebate]" value="<?= @$config['min_rebate'] ?>" class="form-control" placeholder="最小佣金比例">
                                <span class="input-group-addon">%，--</span>
                                <input name="config[max_rebate]" value="<?= @$config['max_rebate'] ?>" class="form-control" placeholder="最大佣金比例">
                                <span class="input-group-addon">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">价格</label>
                    <div class="form-group">
                        <div class="col-xs-2">
                            <div class="input-group">
                                <input name="config[min_price]" value="<?= @$config['min_price'] ?>" class="form-control" placeholder="最小价格">
                                <span class="input-group-addon">--</span>
                                <input name="config[max_price]" value="<?= @$config['max_price'] ?>" class="form-control" placeholder="最大价格">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">优惠券价格</label>
                    <div class="form-group">
                        <div class="col-xs-2">
                            <div class="input-group">
                                <input name="config[min_money]" value="<?= @$config['min_money'] ?>" class="form-control" placeholder="最小价格">
                                <span class="input-group-addon">--</span>
                                <input name="config[max_money]" value="<?= @$config['max_money'] ?>" class="form-control" placeholder="最大价格">
                            </div>
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

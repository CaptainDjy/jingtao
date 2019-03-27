<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/29 11:32
 */

/**
 * @var array $config
 */
$awardArr = [
    ['id' => 1, 'goods_id' => '9', 'type' => 'goods', 'num' => 1, 'title' => '除草剂'],
    ['id' => 2, 'goods_id' => '7', 'type' => 'goods', 'num' => 1, 'title' => '铁铲'],
    ['id' => 3, 'goods_id' => '16', 'type' => 'goods', 'num' => 1, 'title' => '银宝箱'],
    ['id' => 4, 'goods_id' => '10', 'type' => 'goods', 'num' => 1, 'title' => '金宝箱'],
    ['id' => 5, 'goods_id' => '12', 'type' => 'goods', 'num' => 1, 'title' => '低级狗粮'],
    ['id' => 6, 'goods_id' => '0', 'type' => 'addTH', 'num' => 1, 'title' => '碳汇'],
    ['id' => 7, 'goods_id' => '8', 'type' => 'goods', 'num' => 1, 'title' => '浇水壶'],
    ['id' => 8, 'goods_id' => '5', 'type' => 'goods', 'num' => 1, 'title' => '化肥'],
    ['id' => 9, 'goods_id' => '6', 'type' => 'goods', 'num' => 1, 'title' => '阳光增强器'],
    ['id' => 10, 'goods_id' => '0', 'type' => 'addCB', 'num' => 2, 'title' => 'C贝'],
    ['id' => 11, 'goods_id' => '0', 'type' => 'addCB', 'num' => 80, 'title' => 'C贝'],
    ['id' => 12, 'goods_id' => '0', 'type' => 'addCB', 'num' => 1200, 'title' => 'C贝'],
];

?>
<div class="box box-success">
    <div class="box-header with-border">
        签到礼包
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">开关</label>
            <div class="col-xs-12 col-sm-9 col-md-8">
                <label class="radio-inline">
                    <input type="radio" name="config[switch]"
                           value="1" <?= @$config['switch'] == 1 ? 'checked' : ''; ?>>
                    开启
                </label>
                <label class="radio-inline">
                    <input type="radio" name="config[switch]"
                           value="0" <?= empty($config['switch']) ? 'checked' : ''; ?>>
                    关闭
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">每次消耗</label>
            <div class="col-xs-12 col-sm-9 col-md-8">
                <input name="config[consume]" value="<?= @$config['consume'] ?>" class="form-control"
                       placeholder="请输入每次消耗多少C贝">
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">体验次数</label>
            <div class="col-xs-12 col-sm-9 col-md-8">
                <input name="config[freeNum]" value="<?= @$config['freeNum'] ?>" class="form-control"
                       placeholder="请输入次数">

                <span class="help-block">可免费玩的次数</span>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">奖品</label>
            <div class="col-xs-12 col-sm-9 col-md-8">

                <?php foreach ($awardArr as $key => $item): ?>
                    <div class="input-group">
                        <input type="hidden" name="config[award][<?= $key ?>][id]" value="<?= $item['id'] ?>">
                        <input type="hidden" name="config[award][<?= $key ?>][goods_id]"
                               value="<?= $item['goods_id'] ?>">
                        <input type="hidden" name="config[award][<?= $key ?>][type]" value="<?= $item['type'] ?>">
                        <input type="hidden" name="config[award][<?= $key ?>][title]" value="<?= $item['title'] ?>">
                        <input type="hidden" name="config[award][<?= $key ?>][num]" value="<?= $item['num'] ?>">

                        <span class="input-group-addon" style="min-width: 110px;text-align: right"><?= $item['title'] ?>
                            x<?= $item['num'] ?></span>
                        <span class="input-group-addon">概率</span>
                        <input name="config[award][<?= $key ?>][v]" value="<?= @$config['award'][$key]['v'] ?>"
                               class="form-control" placeholder="请输入整数">
                    </div>
                <?php endforeach; ?>

                <span class="help-block">概率和可以超过100，中奖概率是相对于其他奖品来说的</span>
            </div>
        </div>

    </div>
</div>

<?php
/**
 * @var $this yii\web\View
 * @var $id integer
 * @var $configs array
 */

use backend\widgets\MyActiveForm;
use common\helpers\MyHtml;
use common\models\Config;
use yii\bootstrap\Html;
use yii\helpers\Url;

$this->title = '配置修改';
$groups = Config::getConfig('CONFIG_GROUP_LIST');
?>
<div class="nav-tabs-custom">

    <ul class="nav nav-tabs">
        <?php foreach ($groups as $key => $row): ?>
            <li <?= $key == $id ? ' class="active"' : '' ?>>
                <a href="<?= Url::to(['config/group', 'id' => $key]) ?>"><?= $row ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active">
            <div class="box-body">
                <?= Html::beginForm('', 'post', ['class' => 'form-horizontal']); ?>
                <?php foreach ($configs as $config): ?>
                    <?php if ($config['type'] == '1'): ?>
                        <div class="form-group">
                            <label
                                class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"><?= $config['title'] ?></label>
                            <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                                <?= Html::textInput('config[' . $config['name'] . ']', $config['value'], ['class' => 'form-control']); ?>
                                <div class="help-block">【<?= $config['name'] ?>】<?= $config['remark'] ?></div>
                            </div>
                        </div>

                    <?php elseif ($config['type'] == '2'): ?>
                        <div class="form-group">
                            <label
                                class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"><?= $config['title'] ?></label>
                            <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                                <?= Html::textarea('config[' . $config['name'] . ']', $config['value'], ['class' => 'form-control', 'rows' => '3']); ?>
                                <div class="help-block">【<?= $config['name'] ?>】<?= $config['remark'] ?></div>
                            </div>
                        </div>

                    <?php elseif ($config['type'] == '3'): ?>
                        <div class="form-group">
                            <label
                                class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"><?= $config['title'] ?></label>
                            <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                                <?= Html::dropDownList('config[' . $config['name'] . ']', $config['value'], Config::parseConfigAttr($config['extra']), ['class' => 'form-control']); ?>
                                <div class="help-block">【<?= $config['name'] ?>】<?= $config['remark'] ?></div>
                            </div>
                        </div>

                    <?php elseif ($config['type'] == '4'): ?>
                        <div class="form-group">
                            <label
                                class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"><?= $config['title'] ?></label>
                            <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                                <?= Html::radioList('config[' . $config['name'] . ']', $config['value'], Config::parseConfigAttr($config['extra']), ['item' => [MyHtml::className(), 'radioListItem']]); ?>
                                <div class="help-block">【<?= $config['name'] ?>】<?= $config['remark'] ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= Html::submitButton('提交', ['class' => 'btn btn-primary']); ?>
                    </div>
                </div>
                <?php Html::endForm(); ?>
            </div>
        </div>
    </div>
</div>

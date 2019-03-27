<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/13 16:47
 */

/**
 * @var $searchArr array
 */

use common\helpers\MyHtml;
use common\models\Config;
use yii\bootstrap\Html;
use yii\helpers\Url;

?>

<div class="box box-success">
    <div class="box-header with-border">
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
        <?= Html::beginForm(['config/index'], 'get', ['class' => 'form-horizontal']) ?>
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">关键词</label>
            <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                <?= Html::textInput('keywords', $searchArr['keywords'], ['class' => 'form-control']); ?>
                <div class="help-block">请输入配置标识或标题</div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">分组</label>
            <div class="col-xs-12 col-sm-9 col-md-10 col-lg-11">
                <?= Html::checkboxList('group', $searchArr['group'], Config::getConfig('CONFIG_GROUP_LIST'), ['item' => [MyHtml::className(), 'checkboxListItem']]); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">类型</label>
            <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                <?= Html::checkboxList('type', $searchArr['type'], Config::getConfig('CONFIG_TYPE_LIST'), ['item' => [MyHtml::className(), 'checkboxListItem']]); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">状态</label>
            <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                <?= Html::checkboxList('status', $searchArr['status'], [0 => '隐藏', 1 => '显示'], ['item' => [MyHtml::className(), 'checkboxListItem']]); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
            <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                <?= Html::submitButton('提交', ['class' => 'btn btn-success']); ?>
                <a href="<?= Url::toRoute(['config/index']) ?>">
                    <div class="btn btn-default"><i class="glyphicon glyphicon-leaf"></i> 清除筛选</div>
                </a>
            </div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>

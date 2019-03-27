<?php
/**
 * @author pine
 * @copyright Copyright (c) 2018 HNBY Network Technology Co., Ltd.
 * createtime: 2018/05/26 17:00
 */

use common\models\AdvertSpace;
use common\models\Config;
use yii\bootstrap\Html;

/**
 * @var $this \yii\web\View
 * @var $type string
 */
$this->title = '创建' . AdvertSpace::TYPE[$type] . '推广位';
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">

            <?= Html::beginForm(['advert-space/create', 'type' => $type], 'POST', ['class' => 'form-horizontal', 'id' => 'create-form']) ?>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">创建数量</label>
                <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                    <?= Html::textInput('total', '', ['class' => 'form-control', 'placeholder' => '请输入整数']); ?>
                    <?php if ($type == '3'): ?><span class="help-block">拼多多最低10个起</span><?php endif; ?>
                </div>
            </div>

            <?php if ($type == '1'): ?>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">APPID</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= Html::textInput('siteId', Config::getConfig('ALIMAMA_GID'), ['class' => 'form-control', 'placeholder' => '请输入导购ID']); ?>
                        <span class="help-block">淘宝联盟后台->推广管理->媒体管理->导购管理->APPID</span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($type == '2'): ?>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">京东COOKIE</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= Html::textarea('cookie', Config::getConfig('JD_COOKIE'), ['class' => 'form-control', 'placeholder' => '请输入京东COOKIE', 'rows' => 5]); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($type == '1'): ?>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label">阿里妈妈COOKIE</label>
                    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                        <?= Html::textarea('cookie', Config::getConfig('ALIMAMA_COOKIE'), ['class' => 'form-control', 'placeholder' => '请输入阿里妈妈COOKIE', 'rows' => 5]); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label"></label>
                <div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">
                    <?= Html::submitButton('创建', ['class' => 'btn btn-primary']) ?>
                    <?= Html::button('同步', ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
            <?php Html::endForm(); ?>

        </div>
    </div>
</div>


<script>
    <?php $this->beginBlock('footerJs') ?>
    $('form#create-form').bind('submit', function (e) {
        var form = $(this);
        var total = form.find('input[name=total]').val();
        var siteId = form.find('input[name=siteId]').val();
        var cookie = form.find('input[name=cookie]').val();

        var create = function (total, cur) {
            $.post(form.attr('action'), {
                total: total,
                siteId: siteId,
                cookie: cookie,
                cur: cur
            }, function (response) {
                if (response.code === 0) {
                    var msg = '';
                    if (response.data.cur < response.data.total) {
                        msg = '正在创建，';
                        window.setTimeout(function () {
                            create(total, response.data.cur);
                        }, 500)
                    } else {
                        msg = '创建完成！';
                    }
                    $('.layui-layer-content').text(msg + '进度 ' + response.data.cur + '/' + response.data.total);
                } else {
                    $('.layui-layer-content').text(response.msg);
                }
            }, 'json');
        };

        requirejs(['layer'], function(){
            layer.open({
                type: 0,
                title: '请勿关闭窗口',
                content:'开始创建，正在准备，请稍候！',
                btn: false,
                area: ['300px', '150px']
            });
        });

        create(total, 0);

        e.preventDefault();
    });
    <?php $this->endBlock() ?>
</script>

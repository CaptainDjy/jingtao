<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/13 19:05
 */

use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $list yii\db\ActiveQuery
 * @var $keywords string
 */
?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">

            <div class="box-header clearfix no-border">
                <a href="<?= Url::toRoute(['/menu/item-edit']) ?>" data-toggle='modal' data-target='#ajaxModal'
                   class="btn btn-primary pull-left">
                    <i class="fa fa-plus"></i> 添加
                </a>
            </div>

            <table class="table table-bordered table-hover table-striped">
                <thead>
                <tr>
                    <th style="width: 50px">折叠</th>
                    <th>标题</th>
                    <th>路由</th>
                    <th>分组标题</th>
                    <th>状态</th>
                    <th style="width: 80px">排序</th>
                    <th style="width: 150px">操作</th>
                </tr>
                </thead>
                <tbody>
                <?= $this->render('_item-tree', [
                    'list' => $list,
                    'class' => 'r0',
                    'pTitle' => '无',
                    'level' => 0
                ]);
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    <?php $this->beginBlock('footerJs') ?>

    $('input[name=sort]').on('change', function () {
        var sort = $(this).val();
        var id = $(this).attr('data-id');
        if (isNaN(sort)) {
            alert('排序只能输入数字');
            $(this).val(0);
            return false;
        }
        var url = '<?= Url::toRoute('menu/sort') ?>';
        $.post(url, {id: id, sort: sort}, function (response) {
            if (response.code === '0') {
                requirejs(['layer'], function () {
                    layer.msg('排序已更新！', {icon: 1});
                });
            } else if (response.code === '1') {
                requirejs(['layer'], function () {
                    layer.alert(response.msg, {icon: 2})
                });
            } else {
                console.log(response);
            }
        }, 'json')
    });

    $(".fold").on('click', function () {
        var self = $(this);
        var id = self.parent().parent().attr('data-id');

        if (self.hasClass("fa-minus-square")) {
            $('.r' + id).hide();
            self.removeClass("fa-minus-square").addClass("fa-plus-square");
        } else {
            $('.r' + id).show().find(".fa-plus-square").removeClass("fa-plus-square").addClass("fa-minus-square");
            self.removeClass("fa-plus-square").addClass("fa-minus-square");
        }
    });

    <?php $this->endBlock() ?>
</script>

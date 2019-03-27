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
 *
 */

?>
<div class="nav-tabs-custom">
    <?= $this->render('_tabs'); ?>
    <div class="tab-content">
        <div class="tab-pane active">
            <table class="table table-bordered table-hover table-striped">

                <thead>
                <tr>
                    <th style="width: 10px">折叠</th>
                    <th style="width: 200px;">权限名称</th>
                    <th style="width: 200px;">路由地址</th>
                </tr>
                </thead>
                <tbody>
                <?= $this->render('accredit-tree', [
                    'list' => $list,
                    'class' => 'r0',
                    'pTitle' => '无',
                ]);
                ?>
                </tbody>
            </table>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" onclick="history.go(-1)" data-dismiss="modal">返回</button>
                <button class="btn btn-primary">保存</button>
            </div>
        </div>
    </div>
</div>

<script>
    <?php $this->beginBlock('footerJs') ?>

    var checkedInput =<?=json_encode($role_default)?>;
    $("input").each(function (i, data) {
        var tempUrl = $(data).parent().next("td").text();
        var result = $.inArray(tempUrl, checkedInput);
        if (result != -1) {
            $(data).prop("checked", true);
        } else {
            $(data).prop("checked", false);
        }

    })


    $('input[name=sort]').on('change', function () {
        var sort = $(this).val();
        var id = $(this).attr('data-id');
        if (isNaN(sort)) {
            alert('排序只能输入数字');
            $(this).val(0);
            return false;
        }
        var url = '<?= Url::toRoute('auth-manager/item-update') ?>';
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
            //选中
            $('.r' + id).hide();
            $('.r' + id).find("input").prop("checked", 'true');
            self.removeClass("fa-minus-square").addClass("fa-plus-square");
        } else {
            //取消选中
            $('.r' + id).find("input").prop("checked", 'false');
            $('.r' + id).show().find(".fa-plus-square").removeClass("fa-plus-square").addClass("fa-minus-square");
            self.removeClass("fa-plus-square").addClass("fa-minus-square");
        }
    });
    //全选

    $("input").on('click', function () {
        var self = $(this);
        var id = self.parent().parent().attr('data-id');
        var statusChecked = self.prop("checked");
        $('.r' + id).find("input").prop("checked", statusChecked);
    });

    $(".modal-footer button").eq(1).click(function () {
        var ids = [];
        $("input:checked").each(function (i, data) {
            ids.push($(data).parent().parent().attr("data-id") * 1);
        });
        $.post("<?=Url::toRoute('/auth-manager/role-accredit-tree')?>", {
            "data": ids,
            "name": '<?=$name?>'
        }, function (res) {
            console.log("这里是回调函数")
        }, "json");
    })
    <?php $this->endBlock() ?>
</script>

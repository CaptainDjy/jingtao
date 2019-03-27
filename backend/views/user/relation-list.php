<?php

/**
 * @var $this yii\web\View
 * @var $query yii\db\ActiveQuery
 * @var $searchArr array
 */


?>
<div class="tab-content">
    <?= $this->render('_tabs'); ?>
    <div class="panel panel-info">
        <div class="panel-body">
            <div class="form-group">
                <label class="col-lg-1 control-label">微信昵称</label>
                <div class="col-lg-5">
                    <div class="search" style="height: 30px;">
                        <input type="text" id="keyword" name="keyword" value=""
                               style="width: 200px;height: 30px;border-radius: 5px;margin:0 10px;float: left;"
                               placeholder="请输入昵称搜索"/>
                        <p style="line-height: 30px;margin: 0px;float: left;font-size: 14px;">
                            输入昵称自动搜索，可直接拖动操作，请谨慎！！！</p>
                        <div class="clear"></div>
                    </div>
                    <a href="" class="btn  posi"><i class="fa fa-search"></i> 定位</a>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-info">
        <div id="container"></div>
    </div>
</div>

<script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<link rel="stylesheet" href="/static/mobile/js/jstree/themes/default/style.min.css">
<script src="/static/mobile/js/jstree/jstree.min.js"></script>

<script>
    var jstreeObj = $('#container')
        .jstree({
            'core': {
                'data': {
                    'url': "/admin/index.php?r=user%2Frelation-lists&types=get_node",
                    "dataType": "json",
                    'data': function (node) {
                        return {'id': node.id};
                    }
                },
                'check_callback': true,
                'themes': {
                    'responsive': false
                }
            },
            "types": {
                "#": {"max_children": -1, "max_depth": -1, "valid_children": -1},
                "root": {"icon": "/static/mobile/img/iconfont-tree.png", "valid_children": ["default"]},
                "default": {"icon": "/static/mobile/img/iconfont-user.png", "valid_children": ["default", "file"]},
                "file": {"icon": "glyphicon glyphicon-file", "valid_children": []}
            },
            'force_text': true,
            'plugins': ['state', 'dnd', 'search', "types"],//contextmenu
            // 'contextmenu': {
            //     'items':{
            //         "cut" : {
            //             "separator_before"	: false,
            //             "separator_after"	: false,
            //             "label"				: "剪切",
            //             "action"			: function (data) {
            //                 var inst = $.jstree.reference(data.reference),
            //                     obj = inst.get_node(data.reference);
            //                 if(inst.is_selected(obj)) {
            //                     inst.cut(inst.get_top_selected());
            //                 }
            //                 else {
            //                     inst.cut(obj);
            //                 }
            //             }
            //         },
            //         "paste" : {
            //             "separator_before"	: false,
            //             "icon"				: false,
            //             "_disabled"			: function (data) {
            //                 return !$.jstree.reference(data.reference).can_paste();
            //             },
            //             "separator_after"	: false,
            //             "label"				: "粘贴",
            //             "action"			: function (data) {
            //                 var inst = $.jstree.reference(data.reference),
            //                     obj = inst.get_node(data.reference);
            //                 inst.paste(obj);
            //             }
            //         }
            //     }
            // }
        })
        .on('rename_node.jstree', function (e, data) {
            $.get("{php echo $this->createWebUrl('distribution',array('op'=>'listSave','od'=>'rename_node'))}", {
                'id': data.node.id,
                'text': data.text
            })
                .fail(function () {
                    data.instance.refresh();
                });
        })
        .on('move_node.jstree', function (e, data) {
            if (!confirm("确定移动吗，不能回撤？")) {
                data.instance.refresh();
                return;
            }
            $.get("/admin/index.php?r=user%2Frelation-lists&types=move_node", {
                'id': data.node.id,
                'parent': data.parent,
                'position': data.position
            }).fail(function () {
                data.instance.refresh();
            });
        })
        .on('search.jstree', function (e, data) {

        })

    $('#keyword').bind('input propertychange', function () {
        if ($(this).val().length >= 1) {
            jstreeObj.jstree(true).search($(this).val());
            $(".posi").attr("href", $(this).val);
        }
    });

    //定位
    $(".posi").click(function () {
        $(this).attr("href", "#" + $('#keyword').val());
    })

</script>


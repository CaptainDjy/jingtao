<?php
/**
 * 自定义 ActiveForm
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/17 20:50
 */

namespace backend\widgets;

use yii\widgets\ActiveForm;

class MyActiveForm extends ActiveForm
{
    public $options = ['class' => 'form-horizontal'];

    public $fieldConfig = [
        'labelOptions' => ['class' => 'col-xs-12 col-sm-3 col-md-2 col-lg-1 control-label'],
        'template' => '{label}<div class="col-xs-12 col-sm-8 col-md-8 col-lg-6">{input}{hint}{error}</div>',
    ];
}

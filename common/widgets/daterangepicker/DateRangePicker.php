<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/9/25 16:28
 */

namespace common\widgets\daterangepicker;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

class DateRangePicker extends InputWidget
{
    const SEPARATOR = ' 到 ';
    public $template = '{input}';
    public $options = [
        'class' => 'form-control',
        'autocomplete' => 'off',
        'readonly' => 'true',
    ];
    public $icon = 'calendar';

    public $clientOptions = [];
    public $clientEvents = [];

    public function run()
    {
        $this->registerPlugin();

        if (is_string($this->icon)) {
            Html::addCssClass($this->field->options, 'has-feedback');
            $this->template = strtr($this->template, ['{input}' => '{input}' . Html::tag('span', '', [
                    'class' => 'form-control-feedback glyphicon glyphicon-' . $this->icon,
                    'style' => 'right:15px'
                ]),
            ]);
        }

        return strtr($this->template, [
            '{input}' => $this->hasModel()
                ? Html::activeTextInput($this->model, $this->attribute, $this->options)
                : Html::textInput($this->name, $this->value, $this->options),
        ]);
    }

    private function registerPlugin()
    {
        DateRangePickerAsset::register($this->view);

        $id = $this->options['id'];
        $this->clientOptions = ArrayHelper::merge(require 'DateRangePickerConfig.php', $this->clientOptions);
        $options = Json::encode($this->clientOptions);
        $js = "$('#$id').daterangepicker($options)";
        foreach ($this->clientEvents as $event => $handler) {
            $js .= ".on('$event', $handler)";
        }
        $this->view->registerJs($js . ';');
    }
}

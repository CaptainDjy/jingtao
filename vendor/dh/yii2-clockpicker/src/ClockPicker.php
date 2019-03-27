<?php

namespace dh\clockpicker;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * Class Clockpicker
 *
 * @package dh\clockpicker
 * @author zl
 */
class ClockPicker extends InputWidget
{
    public $options = [
        'readonly' => 'true',
        'autocomplete' => 'off',
    ];
    /**
     * @var array options of the JS plugin.
     */
    public $pluginOptions = [];

    public function init()
    {
        parent::init();

        if (!isset($this->pluginOptions['donetext'])) {
            $this->pluginOptions['donetext'] = '确认';
        }

        Html::addCssClass($this->options, 'form-control');

        $this->registerAssets();
    }

    public function run()
    {
        if ($this->hasModel()) {
            return Html::activeTextInput($this->model, $this->attribute, $this->options);
        }

        return Html::textInput($this->name, $this->value, $this->options);
    }

    protected function registerAssets()
    {
        ClockPickerAsset::register($this->view);

        $inputId = "#{$this->options['id']}";
        $pluginOptions = !empty($this->pluginOptions) ? Json::encode($this->pluginOptions) : '{}';
        $this->view->registerJs("jQuery('{$inputId}').clockpicker({$pluginOptions});");
    }
}


<?php

namespace backend\widgets\grid;

use yii\grid\ActionColumn;
use yii\helpers\Html;

class MyActionColumn extends ActionColumn
{
    public $template = '{update}&nbsp;&nbsp;{delete}';

    public $header = '操作';

    public $isText = true;

    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                switch ($name) {
                    case 'view':
                        $title = '查看';
                        break;
                    case 'update':
                        $title = '编辑';
                        break;
                    case 'delete':
                        $title = '删除';
                        break;
                    default:
                        $title = ucfirst($name);
                }
                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0',
                ], $additionalOptions, $this->buttonOptions);

                if ($this->isText) {
                    $icon = $title;
                } else {
                    $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-$iconName"]);
                }

                return Html::a($icon, $url, $options);
            };
        }
    }
}

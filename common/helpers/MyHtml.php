<?php
/**
 * @author
 * @copyright Copyright (c) 2017 HNDH Software Technology Co., Ltd.
 * createtime: 2017/8/18 9:56
 */

namespace common\helpers;

use yii\base\Component;

class MyHtml extends Component
{
    public static function radioListItem($index, $label, $name, $checked, $value)
    {
        unset($index);
        $checked = $checked ? "checked" : "";
        $return = '<label class="radio-inline">';
        $return .= '<input type="radio" name="' . $name . '" value="' . $value . '" ' . $checked . '> ' . $label;
        $return .= '</label>';
        return $return;
    }

    public static function checkboxListItem($index, $label, $name, $checked, $value)
    {
        unset($index);
        $checked = $checked ? "checked" : "";
        $return = '<label class="checkbox-inline">';
        $return .= '<input type="checkbox" name="' . $name . '" value="' . $value . '" ' . $checked . '> ' . $label;
        $return .= '</label>';
        return $return;
    }
}

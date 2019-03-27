<?php

use yii\web\JsExpression;

return [
    'startDate' => new JsExpression('moment().format("YYYY-MM-DD 00:00:00")'),
    'endDate' => new JsExpression('moment()'),
    'timePicker' => true,
    'timePicker24Hour' => true,
    "locale" => [
        "format" => "YYYY-MM-DD HH:mm",
        "separator" => " 到 ",
        "applyLabel" => "确定",
        "cancelLabel" => "取消",
        "fromLabel" => "从",
        "toLabel" => "到",
        "customRangeLabel" => "自定义",
        "weekLabel" => "日",
        "daysOfWeek" => [
            "日",
            "一",
            "二",
            "三",
            "四",
            "五",
            "六"
        ],
        "monthNames" => [
            "一月",
            "二月",
            "三月",
            "四月",
            "五月",
            "六月",
            "七月",
            "八月",
            "九月",
            "十月",
            "十一月",
            "十二月"
        ],
        "firstDay" => 1
    ],
    'ranges' => [
        '今天' => [
            new JsExpression('moment().format("YYYY-MM-DD 00:00:00")'),
            new JsExpression('moment()'),
        ],
        '一周内' => [
            new JsExpression('moment().subtract(1, "weeks")'),
            new JsExpression('moment()'),
        ],
        '二周内' => [
            new JsExpression('moment().subtract(2, "weeks")'),
            new JsExpression('moment()'),
        ],
        '一月内' => [
            new JsExpression('moment().subtract(1, "months")'),
            new JsExpression('moment()'),
        ],
    ]
];

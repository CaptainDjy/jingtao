<?php

namespace backend\controllers;

class UtilsController extends ControllerBase
{
    public function actions()
    {
        return [
            'kindeditor' => [
                'class' => 'common\widgets\kindeditor\KindEditorAction',
            ]
        ];
    }
}

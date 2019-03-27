<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2017/8/17
 * Time: 15:05
 */

namespace backend\widgets\grid;


use yii\grid\DataColumn;

class MyDataColumn extends DataColumn
{
    public $enableSorting = false;
    public $sortLinkOptions = ['class' => 'fa'];
}

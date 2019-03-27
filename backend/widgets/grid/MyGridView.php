<?php

namespace backend\widgets\grid;

use yii\grid\GridView;

class MyGridView extends GridView
{
    public $dataColumnClass = 'backend\widgets\grid\MyDataColumn';
    public $layout = '{items}
    <div class="row">
        <div class="col-xs-5" style="line-height: 30px">
        	{summary}
        </div>
        <div class="col-xs-7">
			{pager}
        </div>
    </div>
    ';

    public $pager = [
        'options' => [
            'class' => 'pagination pagination-sm no-margin pull-right',
        ],
        'firstPageLabel' => '首页',
        'lastPageLabel' => '尾页',
        'prevPageLabel' => ' 上一页',
        'nextPageLabel' => '下一页',
    ];

    public $tableOptions = [
        'class' => 'table table-hover'
    ];

    public $summary = '每页{count}条 - 共{totalCount}条 - 当前{page}/{pageCount}页';

    public function init()
    {
        parent::init();
        $cssString = '.table>thead:first-child>tr:first-child>th>a:after{content:"\f0dc";margin-left:10px;}.table>thead:first-child>tr:first-child>th>a.asc:after{content:"\f160";}.table>thead:first-child>tr:first-child>th>a.desc:after{content:"\f161";}';
        $this->view->registerCss($cssString);
    }
}

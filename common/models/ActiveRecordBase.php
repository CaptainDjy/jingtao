<?php
/**
 * @author
 * @copyright Copyright (c) 2018 HNDH Software Technology Co., Ltd.
 * @link http://www.dhsoft.cn
 */

namespace common\models;

use yii\db\ActiveRecord;

/**
 * Class ActiveRecordBase
 * @property string $error
 * @package common\models
 *
 */
class ActiveRecordBase extends ActiveRecord
{

    public function getError()
    {
        return current($this->getFirstErrors());
    }

}

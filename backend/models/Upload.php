<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/5/18
 * Time: 14:00
 */

namespace backend\models;
use Yii;
use yii\web\UploadedFile;
class Upload extends \yii\db\ActiveRecord
{
    /**
     * @var UploadedFile|Null file attribute
     */
    public $file;
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [["file"], "file",],
        ];
    }
}
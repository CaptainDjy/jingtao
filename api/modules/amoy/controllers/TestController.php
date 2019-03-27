<?php
/**
 * Created by PhpStorm.
 * @author
 * @link http://www.dhsoft.cn
 * Date: 2018/4/28
 * Time: 17:31
 */

namespace api\modules\amoy\controllers;


use common\helpers\Utils;
use common\models\VipCode;

class TestController extends ControllerBase
{
    /**
     * 淘宝客订单查询
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTest()
    {
        return $this->responseJson(1,time());
    }

    public function actionRm(){
        $redis = \Yii::$app->cache->flush();
    }

    /**
     * 生成
     */
    public function actionVipCode()
    {
        /*$price = 1000;

        for ($i=0;$i<1000;$i++)
        {
            $code =  Utils::buildRandom(6);


            while (VipCode::findOne(['code'=>$code])){
                $code = Utils::buildRandom(6);
            }
            $vipcode = new VipCode();
            $vipcode->code = $code;
            $vipcode->price = $price;
            $vipcode->enabled = 0;
            $vipcode->created_at = time();
            $vipcode->save();
        }*/

    }

    public function actionExcel()
    {
        /*$list = VipCode::find()->asArray()->all();
        foreach ($list as $k=>$v){

            $v['enabled'] = $v['enabled'] == 0 ? '未使用' : '已使用';
            $v['created_at'] = date('Y-m-d H:i:s',$v['created_at']);
            $v['updated_at'] = $v['updated_at'] > 0 ? date('Y-m-d H:i:s',$v['updated_at']) : '无';
            $list[$k] = array_values($v);
        }

        //var_export($list);die();
        $this->exportEXCEL($list,['ID','会员码','面值','是否使用','使用者ID','创建时间','使用时间']);*/
    }

    /**
     * @DESC 数据导
     * @example
     *  $data = [[1, "小明", "25"],[2, "王琨", "22"]];
     *  $header = ["id", "姓名", "年龄"];
     *  Myhelpers::exportEXCEL($data, $header);
     * @return void, Browser direct output
     * @throws \PHPExcel_Exception
     */
    public function exportEXCEL($data, $header, $title = "simple", $filename = "data"){
        if (!is_array($data) || !is_array($header)) return false;
        $objPHPExcel = new \PHPExcel();
        // Set properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");

        //设置表头，也就是第一行数据
        foreach ($header as $k => $v){
            $column = \PHPExcel_Cell::stringFromColumnIndex($k);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($column."1", $v);
        }

        //设置行数据，从第二行开始
        foreach ($data as $key=>$item){
            foreach ($item as $key2=>$val){
                $column = \PHPExcel_Cell::stringFromColumnIndex($key2);  //获得列位置
                // 添加一行数据，A1、B1、C1.....N1的各个数据
                //设置为$key+2，因为key是从0开始，而我们的行数据第一个索引是“1”，而上面因为设置了表头，占了一行数据，所以就直接+2
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column.($key+2), $val);
            }
        }

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle($title);
        ob_end_clean();
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');


    }
}
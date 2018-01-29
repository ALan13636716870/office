<?php
/**
 * Created by PhpStorm.
 * User: gaosk
 * Date: 16/7/5
 * Time: 18:23
 */
namespace Backend\Controller;

/**
 * Class ExportAmazonController
 * @package Home\Controller
 */
class SkuController extends CommonController {

    private $list = array();

    public function index($num = 0){
        set_time_limit(0);
        if($num == 0){
            exit('input the num!');
        }
        echo "<pre>";
        for($i=1;$i<=$num;$i++){
            $sku = $this->genSKU();
            echo $sku[0].$sku[1].'-'.$sku[2].$sku[3].$sku[4].$sku[5].'-'.$sku[6].$sku[7].$sku[8].$sku[9]."\n";
        }
    }

    //生成sku
    private function genSKU(){
        $chars = getRandChar(10);
        if(array_key_exists($chars, $this->list)){
            return $this->genSKU();
        }

        $this->list[$chars] = '1';
        $chars = strtoupper($chars);
        return $chars;
    }

}
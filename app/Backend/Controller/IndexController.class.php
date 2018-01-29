<?php
/**
 * Created by PhpStorm.
 * User: gaosk
 * Date: 15/12/17
 * Time: 11:21
 */

namespace Backend\Controller;
use Think\Controller;

class IndexController extends CommonController {
    public function index(){
        $this->display();
    }

    public function lirun(){
    	//售价=售价*15%+16.5(运费)+30+售价* +利润
    	for($start = 50.00;$start<=100;$start+=0.1){
    		$result = number_format(17*$start/20-56.5, 2);
    		echo $start*0.1161.' =====> '.$result*0.1161.'<br />';
    	}
    }
}
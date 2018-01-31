<?php
/**
 * Created by PhpStorm.
 * User: adminditor
 * Date: 2018/1/30
 * Time: 16:15
 */

namespace Backend\Controller;

use Think\Controller;

class DesignController extends Controller
{
    public function index(){
        $design = M('design as d')
            ->field('d.*,e1.title as starp , e2.title as dial , u.name')
            ->join('left join __ELEMENT__ as e1 on e1.id = d.watch_strap and e1.type = "strap" ')
            ->join('left join __ELEMENT__ as e2 on e2.id = d.watch_dial and e2.type = "dial" ')
            ->join('left join __USER__ as u on u.id = d.user_id ')
            ->select();
        $this ->assign('designList',$design);
        $this ->display();
    }
    public function edit(){

    }
    public function design(){

    }
}
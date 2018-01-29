<?php
/**
 * Created by PhpStorm.
 * User: adminditor
 * Date: 2018/1/25
 * Time: 18:33
 */

namespace Backend\Controller;

use Think\Controller;
use Think\Model;
class UserclassController extends Controller
{
    public function index(){
        $userlist = M('user')->select();
        $this ->assign('userlist',$userlist);
        $this ->display();
    }
    //禁用/启用
    public function disable(){
        $user_id = I('get.id',0,'intval');
        $disable = I('get.disable',0,'intval');
        if($disable == 1){
            $err = '启用成功';
        }elseif($disable == 0){
            $err = '禁用成功';
        }else{
            $this ->ajaxreturn(array('result'=>'无效操作','err'=>1));
        }
        if($user_id == 0){
            $this ->ajaxreturn(array('result'=>'请选择用户','err'=>0));
        }
        $data = array('disable'=>$disable);
        $result = M('user') ->where(array('id'=>$user_id))->data($data)->save();

        if($result){
            $this ->ajaxreturn(array('result'=>$err,'err'=>1));
        }else{
            $this ->ajaxreturn(array('result'=>$err,'err'=>0));
        }
    }
}
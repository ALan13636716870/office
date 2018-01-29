<?php
/**
 * Created by PhpStorm.
 * User: adminditor
 * Date: 2018/1/24
 * Time: 21:00
 */

namespace Backend\Controller;

use Think\Controller;
use Think\Model;
class ModelController extends  Controller
{
    public function mould_list(){
        $cat_id = I('get.id',0,'intval');
        $mould = M('mould as m')
            ->join('left join amazon_category as c on c.cat_id = m.cat_id')
            ->join('left join amazon_mould_side as ms on ms.mould_id = m.id')
            ->field(array('m.*','c.*','count(ms.id) as side_nums'))->where('c.cat_id = '.$cat_id)->group('m.id')->select();
        $this -> assign('mouldList',$cat_id);
        $this -> display();
    }
    public function index(){
        $category = M('category as c')->join('left join amazon_mould as m on c.cat_id = m.cat_id')->field('c.cat_id,count(m.id) as mould_nums,c.cat_name')->group('cat_id')->select();
//        $category['mould_num'] = M('mould')->where(array('cat_id'=> $category['cat_id']))->select();

        $this -> assign('categoryList',$category);
        $this -> display();
    }
    public function cat_edit(){
        if(IS_POST){
            $cat_id = I('post.cat_id',0,'htmlspecialchars,trim');
            $display_order = I('post.display_order',0,'htmlspecialchars,trim');
            $name = I('post.name','','htmlspecialchars,trim');

            if($cat_id == 0 || $display_order < 0 || empty($name)){
                $this -> error('请正确填写信息');
            }
//            UPDATE `amazon_category` SET `display_order`='0' WHERE `cat_id` = 1
            $result = M('category') -> data(array('display_order' => $display_order,'cat_name' => $name))->where(array('cat_id'=>$cat_id))->save();
            if($result){
                $this -> success('修改成功','index.php?m=Backend&c=model&a=index');
            }else{
                $this -> error('修改失败');
            }
        }else{
            $cat_id = I('get.id',0,'htmlspecialchars,trim');
            $category = M('category')->where(array('cat_id'=>$cat_id))->find();
            $this-> assign('category',$category);

            $this -> display();
        }
    }

}
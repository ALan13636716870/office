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
        $element_list = M('element as e')->select();

        $this -> assign('element_list',$element_list);
        $this -> display();
    }
//    public function cat_edit(){
//        if(IS_POST){
//            $cat_id = I('post.cat_id',0,'htmlspecialchars,trim');
//            $display_order = I('post.display_order',0,'htmlspecialchars,trim');
//            $name = I('post.name','','htmlspecialchars,trim');
//
//            if($cat_id == 0 || $display_order < 0 || empty($name)){
//                $this -> error('请正确填写信息');
//            }
////            UPDATE `amazon_category` SET `display_order`='0' WHERE `cat_id` = 1
//            $result = M('category') -> data(array('display_order' => $display_order,'cat_name' => $name))->where(array('cat_id'=>$cat_id))->save();
//            if($result){
//                $this -> success('修改成功','index.php?m=Backend&c=model&a=index');
//            }else{
//                $this -> error('修改失败');
//            }
//        }else{
//            $cat_id = I('get.id',0,'htmlspecialchars,trim');
//            $category = M('category')->where(array('cat_id'=>$cat_id))->find();
//            $this-> assign('category',$category);
//
//            $this -> display();
//        }
//    }
    public function element_edit(){
        if(IS_POST){
            $id= I('post.id',0,'intval');
            $title= I('post.title','','htmlspecialchars,trim');
            $width= I('post.width',0,'intval');
            $height= I('post.height',0,'intval');
            $type= I('post.type',1,'intval');
            $photo= I('post.photo','','htmlspecialchars,trim');
            $status= I('post.status',0,'intval');
            $display_order= I('post.display_order',0,'intval');
            $data =array(
                'title' => $title,
                'width' => $width,
                'height'=> $height,
                'type'  => $type,
                'photo' => $photo,
                'status' => $status,
                'display_order' => $display_order
            );
//            var_dump($data);exit();
            if($id == 0){
                $element_list = M('element')-> data($data)->add();
            }else{
                $element_list = M('element')-> data($data)-> where(array('id'=>$id))->save();
            }
            if($element_list) {
                $this -> success('操作成功',U('model/index'));
            }else{
                $this -> error('操作失败');
            }
        }else{
            $id= I('get.id',0,'intval');
            if($id == 0){
                $this ->display();
            }else{
                $element_list = M('element')->where(array('id'=>$id))->find();
                $this -> assign('element',$element_list);
                $this ->display();
            }


        }
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: adminditor
 * Date: 2018/1/29
 * Time: 9:42
 */

namespace Backend\Controller;

use Think\Controller;

class AttributeController extends Controller
{
    public function index(){
        $attr = M('attribute as a')->join('left join __ATTRIBUTE_VALUE__ as av on a.default = av.id ')->field('a.*,av.attr_value as default_value ')->select();
//        foreach ($attr as $key =>$val){
////            $attr[$key]['is_default'] = M('attr_')
//        }
        $this ->assign('attrList',$attr);

        $this ->display();
    }
    public function edit(){
        if(IS_POST){
            $id = I('post.id',0,'intval');

            $data = array(
                'attr_name' => I('post.attr_name','','htmlspecialchars,trim'),
                'default'   => I('post.default','','intval')
            );
            if($id == 0){
                $result = M('attribute') -> data($data) ->add();
            }else{
                $result = M('attribute')-> where(array('id' => $id))->data($data)->save();
            }
            if($result){
                $this->success('操作成功！', U('attribute/index'));
            }else{
                $this -> error('操作失败');
            }
        }else{
            $id = I('get.id',0,'intval');
            if($id == 0){
                $this ->error('请选择属性');
            }
            $attr = M('attribute')->where(array('id'=> $id))->find();
            $attr_value = M('attribute_value')->where(array('attr_id' => $id))->select();
            $this -> assign('attr',$attr);
            $this -> assign('attr_value',$attr_value);
            $this ->display();
        }

    }
    public function attr_value_list(){
        $id = I('get.id',0,'intval');
        if($id == 0){
            $attrvalueList = M('attribute_value as av')->join('left join __ATTRIBUTE__  as a on a.id = av.attr_id ')-> order('av.attr_id desc')->select();
        }else{
            $attrvalueList = M('attribute_value as av') ->join('left join __ATTRIBUTE__  as a on a.id = av.attr_id ')-> order('av.attr_id desc')->where(array('av.attr_id'=> $id))->select();
        }
        $this -> assign('attr_list',$attrvalueList);
        $this ->display();
    }
    public function value_edit(){
        $id = I('get.id',0,'intval');
        $value = M('attribute_value')->where(array('id'=>$id))->find();
        $this-> assign('value_list',$value);
        $this->display('value_edit');
    }
}
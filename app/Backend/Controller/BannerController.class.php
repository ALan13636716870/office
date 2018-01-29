<?php
/**
 * Created by PhpStorm.
 * User: adminditor
 * Date: 2018/1/25
 * Time: 16:25
 */

namespace Backend\Controller;

use Think\Controller;
use Think\Model;
class BannerController extends Controller
{
    public function index(){
        $banner = M('banner') ->select();
        $this->assign('banner_list',$banner);
        $this ->display();
    }
    public function del(){
        $banner_id = I('post.banner_id',0,'intval');
        if($banner_id == 0){
            $this->error('请选择图片');
        }
        $result = M('banner')->where(array('banner' => $banner_id))->delete();
        if($result){
            $this ->success('删除成功',U("banner/index"));
        }else{
            $this->error('删除失败');
        }
    }
    public function edit($id = 0){

        if($id == 0){
            $this->error('请选择图片');
        }
        $banner = M('banner') ->where(array('banner_id' =>$id)) ->find();
        $this ->assign('banner',$banner);
        $this -> display();
    }
    public function modify_edit(){
        $id = I('post.banner_id',0,'intval');
        if($id == 0){
            $is_add = true;
        }else{
            $is_add = false;
        }
        $data = array(
            'title' => I('post.title','','htmlspecialchars,trim'),
            'url'   => I('post.url','','htmlspecialchars,trim'),
            'is_enable' => I('post.url','','htmlspecialchars,trim'),
            'old_time' => I('post.old_time','','intval'),
            'add_time' => I('post.add_time','','intval'),
            'display_order' => I('post.display_order','','htmlspecialchars,trim'),
        );
        if($is_add){
            $result = M('banner')->add($data);
        }else{
            $result = M('banner') ->where(array('banner_id'=>$id))->save($data);
        }
        if($result){
            $this ->success('操作成功',U("banner/index"));
        }else{
            $this->error('操作失败');
        }
    }
}
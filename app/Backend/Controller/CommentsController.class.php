<?php
/**
 * Created by PhpStorm.
 * User: adminditor
 * Date: 2018/1/26
 * Time: 16:42
 */

namespace Backend\Controller;

use Think\Controller;

class CommentsController extends Controller
{
    public function index(){
        $comment = M('comment as c')
            ->join('left join amazon_user as u on c.user_id = u.id')
            ->join('left join amazon_goods as g on g.goods_id = c.goods_id')
            ->field('c.*,u.username,u.id as user_id,u.email,g.goods_name')->select();

        foreach ($comment as $key => $val){
            $comment[$key]['fromatt_add_time'] = date('Y-m-d',$val['add_time']);
        }
        $this -> assign('commitList',$comment);
        $this -> display();
    }
    public function edit_enable(){
        $id =I('get.id',0,'intval');
        $is_enable = I('get.is_enable',0,'intval');
        $result = M('comment') -> data(array('is_enable'=>$is_enable))->where(array('id'=>$id))->save();

        if($result){
            $this ->ajaxreturn(array('result'=>'修改成功','err'=>1));
        }else{
            $this ->ajaxreturn(array('result'=>'修改失败','err'=>0));
        }
    }
    public function edit(){
        if(IS_POST){

            $is_add = I('post.is_add',0,'intval');
            $cat_id = I('post.cat_id',1,'intval');
            $content = I('post.content','','htmlspecialchars,trim');
            $id = I('post.id',0,'intval');
            $user_name= I('post.user_name','','htmlspecialchars,trim');
            $user_id = I('post.user_id',0,'intval');
            if($user_id == 0){
                $user_id = M('user')->where(array('username'=> $user_name))->field('id')->find()['id'];
            }
//            var_dump($content);exit;
            if(!$user_id){
                $this ->error('用户名称填写错误');
            }
            if(empty($content)){
                $this ->error('评论内容不能为空');
            }
//            var_dump($is_add);exit();
            if($is_add == 0){
                $data = array(
                    'user_id' =>$user_id['id'],
                    'content'=>$content,
                    'is_enable' => '1',
                    'cat_id' =>$cat_id
                );
                $result = M('comment')->data($data)->where('id='.$id)->save();
//                var_dump(M('comment')->getLastSql());exit();
            }else{
                $data = array(
                    'user_id' =>$user_id['id'],
                    'content'=>$content,
                    'add_time'=>time(),
                    'is_enable' => '1',
                    'cat_id' =>$cat_id
                );
                $result = M('comment')->data($data)->add();
            }
            if($result){
                $this ->success('操作成功',U('comments/index'));
            }else{
                $this ->error('操作失败');
            }

        }else{
            $id = I('get.id',0,'intval');
            if($id <> 0){
                $comment = M('comment as c')
                    ->join('left join amazon_user as u on c.user_id = u.id')
                    ->join('left join amazon_goods as g on g.goods_id = c.goods_id')
                    ->field('c.*,u.username,u.id as user_id,u.email,g.goods_name')
                    ->where(array('c.id'=>$id))
                    ->find();
                $this ->assign('is_add','0');
                $this ->assign('comment',$comment);
            }else{
                $this ->assign('is_add','1');
            }
            $goods = M('goods')->field('goods_id,goods_name')->select();
            $this ->assign('goods_list',$goods);
            $this ->display();
        }
    }
    public function add(){
        $cat = M('category')->field('cat_id,cat_name')->select();
        $this ->display('edit');
    }
    public function del(){
        $id = I('get.id',0,'intval');
        if($id == 0 ){
            $this ->error('请选择评论');
        }
        $result = M('comment')->where('id='.$id)-> delete();
        if($result){
            $this ->success('操作成功',U('comments/index'));
        }else{
            $this ->error('操作失败');
        }
    }
}
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
        $comment = M('review as c')
//            ->join('left join amazon_user as u on c.user_id = u.id')
//            ->join('left join amazon_goods as g on g.goods_id = c.goods_id')
            ->field('c.*')->select();

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
            $content = I('post.content','','htmlspecialchars,trim');
            $id = I('post.id',0,'intval');
            $add_time = I('post.add_time','2018-01-01','htmlspecialchars,trim');
            $photo = I('post.photo','','htmlspecialchars,trim');
            $user_name= I('post.user_name','','htmlspecialchars,trim');
            $goods_name = I('post.goods_name','','htmlspecialchars,trim');
            $rating = I('post.rating',0,'intval');
            $title = I('post.title','','htmlspecialchars,trim');
            if(!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$add_time)){
                $this ->error('时间格式错误');
            }
            if(empty($content)){
                $this ->error('评论内容不能为空');
            }

            $str_photo = '';
//            var_dump($photo);
            if(is_array($photo)){
                foreach ($photo as $key =>$val){
                    $str_photo .= $val." , ";

                }
            }
            $str_photo =rtrim($str_photo, " , ");
//            var_dump($str_photo);exit;
            if(empty($photo)){
                $str_photo ='';
            }
            if($id <> 0){
                $data = array(
                    'username' =>$user_name,
                    'goods_name' => $goods_name,
                    'rating'=> $rating,
                    'title'=> $title,
                    'content'=> $content,
                    'photo' => $str_photo,
                    'add_time'=> strtotime($add_time)
                );
//                var_dump($data);exit();
                $result = M('review')->data($data)->where('id='.$id)->save();
//                var_dump(M('comment')->getLastSql());exit();
            }else{
                $data = array(
                    'username' =>$user_name,
                    'goods_name' => $goods_name,
                    'rating'=> $rating,
                    'title'=> $title,
                    'content'=> $content,
                    'photo' => $str_photo,
                    'add_time'=> strtotime($add_time)
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
                $comment = M('review as c')
                    ->field('c.*')
                    ->where(array('c.id'=>$id))
                    ->find();
//                $comment['photo'] = explode(', ',$comment['photo']);
                $this ->assign('comment',$comment);
            }

            $goods = M('goods')->field('id,title')->select();
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
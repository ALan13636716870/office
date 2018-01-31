<?php
/**
 * Created by PhpStorm.
 * User: adminditor
 * Date: 2018/1/24
 * Time: 20:59
 */

namespace Backend\Controller;

use Think\Controller;
use Think\Model;
class GoodsModelController extends Controller
{
    public function index(){
        $id = I('get.id',0,'intval');

        //分页
//        $page = I('get.page', 0, 'intval');
//        $page = max(1, $page);
//        $pagesize = 100;
//        $offset = ($page-1)*$pagesize;
        if($id <>0){
            $where = array('mould_id' =>$id);
        }else{
            $where = array();
        }
//        $count = M('goods')->where($where)->count();
//        $pager = new \Think\Page($count, $pagesize, array());
//        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
//        $pageStr = $pager->show();
//        $this->assign('pageStr', $pageStr);

        $goodsList = M('goods as g')->where($where)
            ->join('left join __MOULD__ as m on m.id = g.mould_id ')
            ->join('left join __ELEMENT__ as e1 on e1.id = g.watch_strap and e1.type = "strap" ')
            ->join('left join __ELEMENT__ as e2 on e2.id = g.watch_dial and e2.type = "dial" ')
            ->field('m.name as mould,e1.title as strap,e2.title as dial,g.*')
           ->select();
        foreach ($goodsList as $key => $val){
            $goodsList[$key]['format_price'] = sprintf('$%s',number_format($val['price'], 2, '.', ''));
//            $goodsList[$key]['mould'] =
            if($val['category'] == 1){
                $goodsList[$key]['category_value'] = '情侣';
            }elseif($val['category'] == 2){
                $goodsList[$key]['category_value'] = '家庭';
            }else{
                $goodsList[$key]['category_value'] = '';
            }

        }
//        $this->assign('keyword', $keyword);
//        $this->assign('asin', $asin);
//        $this->assign('status', $status);

        $this->assign('goodsList', $goodsList);
        $this->assign('mould_id', $id);
        $this->display();
    }
    public function edit($id = 0){
        $mould_id = I('post.mould', 0, 'intval');
        if(intval($id) <> 0){
            $goods = M('goods as g')->where(array('id' => intval($id)))->find();
            if(!$goods){
                $this -> error('无此商品');
            }
            $this -> assign('goods',$goods);
        }
        $mould = M('mould')->select();
        $strap = M('element')->where(array('type' => 'strap')) ->select();
        $dial = M('element')->where(array('type' => 'dial')) ->select();

        $this->assign('strapList',$strap);
        $this->assign('mould_id',$mould_id);
        $this->assign('mouldList',$mould);
        $this->assign('dialList',$dial);
        $this ->display();
    }
    public function modify_edit(){
        $name = I('post.name', 0, 'htmlspecialchars,trim');
        $description = I('post.description', '', 'trim');
        $id = I('post.id', 0, 'intval');
        $mould = I('post.mould', 0, 'intval');
        $data = array(
            'name'=>$name,
            'description'=>$description,
            'title' => I('post.title','','htmlspecialchars,trim'),
            'mould_id'=> I('post.mould_id','','htmlspecialchars,trim'),
            'watch_strap'=> I('post.watch_strap','','htmlspecialchars,trim'),
            'watch_dial'=> I('post.watch_dial','','htmlspecialchars,trim'),
            'price'=> I('post.price','','htmlspecialchars,trim'),
            'photo'=> I('post.photo','','htmlspecialchars,trim'),
            'factory_photo'=> I('post.factory_photo','','htmlspecialchars,trim'),
            'photo_view'=> I('post.photo_view','','htmlspecialchars,trim'),
            'category' =>I('post.category','','htmlspecialchars,trim'),
        );
//        var_dump($data);exit();
        if($id == 0){
            $result = M('goods')->data($data)->add();
        }else{
            $result = M('goods')->data($data)->where(array('id'=>$id))->save();
        }
        if($result){
            if($mould){
                $this->success('操作成功！', U('goodsModel/index'));
            }else{
                $this->success('操作成功！', U('goodsModel/index',array('id'=>$mould)));
            }

        }else{
            $this -> error('操作失败');
        }

    }
    public function del($id){
        $result = M('goods')->where(array('id'=>$id))->delete();
        if ($result){
            $this->success('操作成功！', U('goodsModel/index'));
        }else{
            $this -> error('操作失败');
        }
    }

}
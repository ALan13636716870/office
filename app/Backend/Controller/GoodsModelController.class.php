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
        $timeline = I('get.timeline', 0, 'intval');

        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize = 100;
        $offset = ($page-1)*$pagesize;

        $where = array();
        $count = M('goods')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, array());
        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $goodsList = M('goods as g')->where($where)
            ->join('left join amazon_category as c on c.cat_id = g.cat_id ')->field('c.cat_name,g.*')
            ->order('g.add_time desc, g.goods_id desc')->limit($offset, $pagesize)->select();
        foreach($goodsList as $k => $v){
            $goodsList[$k]['goods_img'] = str_replace('_AC_US160_.', '', $v['goods_img']);
            $goodsList[$k]['goods_img'] = C('OSS_HOST').'/'. $goodsList[$k]['goods_img'];
        }

//        $this->assign('keyword', $keyword);
//        $this->assign('asin', $asin);
//        $this->assign('status', $status);

        $this->assign('goodsList', $goodsList);
        $this->display();
    }
    public function edit($id = 0){
        if($id == 0){
            $cat = M('category')->field('cat_id,cat_name')->select();
            $this ->assign('catList',$cat);
            $this ->display();
        }else{
            $cat = M('category')->field('cat_id,cat_name')->select();
            $goodsList = M('goods as g')->where(array('g.goods_id'=> $id))
                ->join('left join amazon_category as c on c.cat_id = g.cat_id ')->field('g.*')
                ->order('g.add_time desc, g.goods_id desc')->find();

            $this ->assign('catList',$cat);
            $this ->assign('goods',$goodsList);
            $this ->display();
        }

    }
    public function modify_edit(){
        $goods_name = I('post.goods_name', 0, 'htmlspecialchars,trim');
        $goods_sn = I('post.goods_sn', 0, 'htmlspecialchars,trim');
        $design_photo = I('post.design_photo', 0, 'htmlspecialchars,trim');
        $display_order = I('post.display_order', 0, 'intval');
        $id = I('post.id', 0, 'intval');
        $cat_id = I('post.cat_id', 0, 'intval');

        $data = array(
            'goods_name'=>$goods_name,
            'goods_sn'=>$goods_sn,
            'design_photo'=>$design_photo,
            'display_order'=> $display_order,
            'cat_id'=>$cat_id
        );
        if($id == 0){
            $result = M('goods')->data($data)->add();
            $is_edit = false;
        }else{
            $result = M('goods')->data($data)->where(array('goods_id'=>$id))->save();
            $is_edit = true;
        }
        if($result){
            $this->success('操作成功！', U('goodsModel/index'));
        }else{
            $this -> error('操作失败');
        }
    }
    public function del($id){
        $result = M('goods')->where(array('goods_id'=>$id))->delete();
        if ($result){
            $this->success('操作成功！', U('goodsModel/index'));
        }else{
            $this -> error('操作失败');
        }
    }

}
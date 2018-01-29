<?php
/**
 * Created by PhpStorm.
 * User: adminditor
 * Date: 2018/1/25
 * Time: 20:38
 */

namespace Backend\Controller;

use Think\Controller;
use Think\Model;
class FlowController extends Controller
{
    public function index(){
        $user_id = I('get.id',0,'intval');
        if($user_id == 0){
            $flow = M('order_info as o')
                ->join('left join amazon_user as u on u.id = o.user_id')
                ->join('left join amazon_design as d on d.order_id = o.order_id')
                ->field('o.*,u.username,u.email,count(d.id) as nums')
                ->select();
        }else{
            $flow = M('order_info as o')
                ->join('left join amazon_user as u on u.id = o.user_id')
                ->join('left join amazon_design as d on d.order_id = o.order_id')
                ->field('o.*,u.username,u.email,count(d.id) as nums')
                ->where(array('o.user_id'=>$user_id))->select();

        }
        foreach ($flow as $key => $val){
            $flow[$key]['formatted_goods_price']= $this ->price_format($val['goods_price'],false);
            $flow[$key]['is_sub'] = $this -> sub($val['is_submit']);
        }


        $this ->assign('id',$user_id);
        $this ->assign('flowList',$flow);
        $this -> display();
    }
    private function sub($sub){
        if($sub == C('NOT_PAY')){
            return '未付款';
        }elseif($sub == C('DO_PAY')){
            return '已付款';
        }elseif($sub == C('DO_SHIP')){
            return '已发货';
        }else{
            return '参数错误';
        }
    }
    public function edit(){
        $id = I('get.id',0,'intval');
        if($id == 0){
            $this ->error('请选择订单');
        }
        $user = M('user as u')
            ->join('LEFT JOIN amazon_order_info as o ON u.id = o.user_id')
            ->where(array('o.order_id'=> $id))->field('u.*')->find();
        $order = M('order_info ')
//            ->join('LEFT JOIN amazon_order_info as o ON u.id = o.user_id')
            ->where(array('order_id'=> $id))->find();
        $order['formatted_goods_price']= $this ->price_format($order['goods_price'],false);
        $design = M('design')
//            ->join('LEFT JOIN amazon_order_info as o ON u.id = o.user_id')
            ->where(array('order_id'=> $id))->select();
        foreach ($design as $item =>$val) {
            $design[$item]['formatted_design_photo']= C('OSS_IMG_HOST').'/'.$val['design_photo'];
            $design[$item]['formatted_effect_photo']= C('OSS_IMG_HOST').'/'.$val['effect_photo'];
        }

        $this ->assign('user',$user);
        $this ->assign('order',$order);
        $this ->assign('designList',$design);

        $this ->display();
    }

    private function price_format($price, $change_price = true)
    {
        if($price==='')
        {
            $price=0;
        }

        if ($change_price && defined('ECS_ADMIN') === false)
        {
            $price = number_format($price, 2, '.', '');
        }
        else
        {
            $price = number_format($price, 2, '.', '');
        }

        return sprintf('$%s', $price);
    }
    public function invoice_no(){
        $id = I('get.order_id',0,'intval');
        $invoice_no = I('get.invoice_no','','intval');
        $data = array('invoice_no'=>$invoice_no);
        $result = M('order_info')->data($data)->where(array('order_id'=>$id))->save();
        if($result){
            $this -> ajaxreturn(array('result'=> '操作成功','err'=> 1));
        }else{
            $this -> ajaxreturn(array('result'=> '操作失败','err'=> 0));
        }
    }
    public function do_order(){
        $user_id = I('post.user_id',0,'intval');
        $order_id = I('post.order_id',0,'intval');
        $order = M('order_info')->where(array('order_id'=>$order_id))->find();
        if($order['is_submit'] == 0){
            $submit = 1;
            $sub = '付款';
        }elseif($order['is_submit'] == 1){
            $submit = 2;
            $sub = '发货';
        }elseif($order['is_submit'] == 2){
            $this ->error('无法重复发货');
        }else{
            $this ->error('参数错误');
        }
        $result = M('order_info')->where(array('order_id' => $order_id))->save(array('is_submit' => $submit));
        if ($result){
            $this -> success($sub.'成功',U('flow/index'));
        }else{
            $this -> error($sub.'失败');
        }
    }
}
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
            $flow = M('order as o')
                ->field('o.*,u.name as user_name,u.email,count(oi.id) as nums')
                ->join('left join __USER__ as u on u.id = o.user_id  ')
                ->join('left join __ORDER_ITEM__ as oi on oi.order_id = o.id  ')
                ->group('o.id desc')
                ->select();
        }else{
            $flow = M('order as o')
                ->field('o.*,u.name as user_name,u.email,count(oi.id) as nums')
                ->join('left join __USER__ as u on u.id = o.user_id  ')
                ->join('left join __ORDER_ITEM__ as oi on oi.order_id = o.id  ')
                ->group('o.id desc')->where(array('u.id'=>$user_id))
                ->select();
        }
//        $flow = M('order_item as oi')
//            ->field('oi.*,u.name as user_name ,u.email , e1.title as strap ,e2.title as dial,g.title as goods_name')
//            ->join('left join __USER__ as u on u.id = oi.user_id  ')
//            ->join('left join __ELEMENT__ as e1 on e1.id = oi.watch_strap and e1.type = "strap" ')
//            ->join('left join __ELEMENT__ as e2 on e2.id = oi.watch_dial and e2.type = "dial" ')
//            ->join('left join __GOODS__ as g on g.id = oi.goods_id  ')
//            ->select();

        foreach ($flow as $key => $val){
//            $flow[$key]['format_purchase_date'] = date('')
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
        $user = M('order as o')
            ->field('u.*')
            ->join('left join __USER__ as u on u.id = o.user_id ')
            ->group('o.id desc')->where(array('o.user_id'=>$id))
            ->find();
        $order = M('order as o')
            ->field('o.*')
//            ->join('left join __NATION__ as n on n.code_two = o.country_code ')
            ->where(array('o.id'=>$id))
            ->group('o.id desc')
            ->find();
        $goods = M('order as o')
            ->field('oi.*,g.title as goods_name ,d.factory_photo as design_photo,e1.title as strap,e2.title as dial')
            ->join('left join __ORDER_ITEM__ as oi on oi.order_id = o.id ')
            ->join('left join __GOODS__ as g on oi.goods_id = g.id ')
            ->join('left join __DESIGN__ as d on d.id = oi.design_id ')
            ->join('left join __ELEMENT__ as e1 on e1.id = oi.watch_strap and e1.type = "strap" ')
            ->join('left join __ELEMENT__ as e2 on e2.id = oi.watch_dial and e2.type = "dial" ')
            ->group('o.id desc')->where(array('o.id' =>$id))
            ->select();
        $countgoods = 0;
        foreach ($goods as $key => $val){
            $countgoods += $val['quantity_ordered'];
        }
        $order['countgoods'] =$countgoods;
        $this ->assign('user',$user);
        $this ->assign('order',$order);
        $this ->assign('goodsList',$goods);

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
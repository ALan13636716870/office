<?php
/**
 * Created by PhpStorm.
 * User: gaosk
 * Date: 16/2/3
 * Time: 14:29
 */
namespace Backend\Controller;

use Think\Exception;

class GoodsController extends CommonController {

    public function index(){
        $timeline = I('get.timeline', 0, 'intval');

        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize = 100;
        $offset = ($page-1)*$pagesize;

        $where = array();

        $asin = I('get.asin','','trim');
        if(!empty($asin)){
            $where['asin'] = $asin;
        }else{
            $status = I('get.status','','trim');
            if($status != ''){
                $where['status'] = $status;
            }
        }

        $keyword = I('get.keyword','','trim');
        if(!empty($keyword)){
            $where['_string'] = 'MATCH(title) AGAINST(\''.$keyword.'\' IN BOOLEAN MODE)';
        }

        if(!empty($timeline)){
            $this->assign('timeline', $timeline);
            $where['add_time'] = $timeline;
        }

        $count = M('popsocket')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, array());
        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $goodsList = M('popsocket')->where($where)
            ->order('add_time desc, id desc')->limit($offset, $pagesize)->select();
        foreach($goodsList as $k => $v){
            $goodsList[$k]['photo'] = str_replace('_AC_US160_.', '', $v['photo']);
        }

        $this->assign('keyword', $keyword);
        $this->assign('asin', $asin);
        $this->assign('status', $status);
        $this->assign('goodsList', $goodsList);
        $this->display();
    }

    public function status(){
        $asin = I('get.asin','','trim');
        $status = I('get.status',0,'intval');

        M('popsocket')->where(array('asin'=>$asin))->save(array('status'=>$status));

        $this->ajaxReturn(array('status'=>'1'));
        exit;
    }

    public function export(){
        set_time_limit(0);

        $asin = I('get.asin','','trim');
        $where = array();
        if(!empty($asin)){
            $where['asin'] = $asin;
        }else{
            $status = I('get.status','','trim');
            if($status != ''){
                $where['status'] = $status;
            }
            
            $keyword = I('get.keyword','','trim');
            if(!empty($keyword)){
                $where['_string'] = 'MATCH(title) AGAINST(\''.$keyword.'\' IN BOOLEAN MODE)';
            }

            $timeline = I('get.timeline', 0, 'intval');
            if(!empty($timeline)){
                $this->assign('timeline', $timeline);
                $where['add_time'] = $timeline;
            }
        }

        $goodsList = M('popsocket')->where($where)->field('asin')->select();
        echo '<pre>';
        foreach($goodsList as $k => $v){
            echo $k."\t".$v['asin']."\n";
            $index++;
        }
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: gaosk
 * Date: 16/2/3
 * Time: 14:29
 */
namespace Backend\Controller;

use Think\Exception;

class HaoyeController extends CommonController {

    public function index(){
        $this->display();
    }

    private function getbk($t){
        $info = M('data_badword')->where(array('t'=>$t))->find();
        $klist = array_filter(preg_split('/[\n\r]+/', $info['keywords']));
        foreach($klist as $k => $v){
            $klist[$k] = addslashes($v);
        }
        $pattern = implode('|', $klist);

        return $pattern;
    }

    public function bad(){
        if(IS_GET){
            $t = I('get.t', '', 'trim');
            $info = M('data_badword')->where(array('t'=>$t))->find();
            if(empty($info)){
                $info = array('t'=>$t);
            }

            $this->assign('info', $info);
            $this->display();
        }else{
            $t = I('post.t', '', 'trim');
            $keywords = I('post.keywords', '', 'trim');
            $info = M('data_badword')->where(array('t'=>$t))->find();
            if($info){
                M('data_badword')->where(array('t'=>$t))->save(array('keywords'=>$keywords));
            }else{
                M('data_badword')->add(array('keywords'=>$keywords,'t'=>$t));
            }
            $this->success('设置成功', U('haoye/index'));
        }
    }


    public function d080113(){

        $k = I('get.k', '', 'trim');
        $batch = I('get.batch', '', 'intval');
        $market = I('get.market', 0, 'intval');

        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize = 200;
        $offset = ($page-1)*$pagesize;

        $where = array();
        if(!empty($market)){
            $where['marketplace_id'] = $market;
        }
        $strArr = array();
        if(!empty($k)){
            $strArr[] = 'MATCH(keywords) AGAINST(\''.$k.'\' IN BOOLEAN MODE)';
        }
        if(!empty($batch) and is_numeric($batch)){
            $strArr[] = 'MATCH(batch) AGAINST(\'+'.$batch.'\' IN BOOLEAN MODE)';
        }
        if(!empty($strArr)){
            $where['_string'] = implode(' and ', $strArr);
        }

        $count = M('data_180103_new')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, array());
        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();

        $caseList = M('data_180103_new')->where($where)->order('id asc')->limit($offset, $pagesize)->order('search_count desc')->select();
        $marketArr = array(
            1=>'US',
            3=>'UK',
            4=>'DE',
            5=>'FR',
            6=>'JP',
            7=>'CA',
            3240=>'CN',
            35691=>'IT',
            44551=>'ES',
            44571=>'IN',
            111172=>'AU',
            328451=>'NL',
            526970=>'BR',
            771770=>'MX'
        );

        $batchArr = array(
            2001=>'皮套手机壳',
            2002=>'魔术贴臂章',
            2003=>'成品钱包',
            2004=>'可弯曲物品',
            2005=>'护具',
            2006=>'钱包挂钩',
            2007=>'浴帘挂钩',
            2008=>'智能手表',
            2009=>'手机壳',
            2010=>'眼镜盒',
            2011=>'眼罩',
            2012=>'枕头',
            2013=>'手镯',
            2014=>'手袋',
            2015=>'钱包夹',
            2016=>'手表',
            2017=>'行李标签',
            2018=>'睡眠面罩',
            2019=>'眼膜',
            2020=>'帽子',
            2021=>'飞盘',
            2022=>'T恤',
            2023=>'鼠标垫',
            2024=>'睡眠面膜',
            2025=>'手机袋',
            2026=>'青少年女孩礼物',
            2027=>'鼠标垫',
            2028=>'苹果表带',
            2029=>'T恤',
            2030=>'睡眠眼罩',
            2031=>'手提袋挂钩',
            2032=>'帽子',
            2033=>'睡眠面罩',
            2034=>'壳（手机,电脑)',
            2035=>'钱包,钱包夹',
            2036=>'壳（手机,电脑)',
            2037=>'壳（手机,电脑)',
            2038=>'腕表',
            2039=>'帽子',
            2040=>'鼠标垫',
            2041=>'狗牌',
            2042=>'枕套',
            2043=>'玩偶',
            2044=>'手机壳',
            2045=>'鼠标垫',
            2046=>'无线充',
            2047=>'护照套',
            2048=>'袜子',
            2049=>'羽绒被套',
            2050=>'T恤'
        );

        $this->assign('k', $k);
        $this->assign('batch', $batch);
        $this->assign('pageStr', $pageStr);
        $this->assign('caseList', $caseList);
        $this->assign('batchArr', $batchArr);
        $this->assign('market', $market);
        $this->assign('marketArr', $marketArr);
        $this->display();
    }


    public function phone(){

        $k = I('get.k', '', 'trim');

        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize = 1000;
        $offset = ($page-1)*$pagesize;

        $where = array();
        if(!empty($k)){
            $where['keywords'] = array('like', '%'.$k.'%');
        }

        $count = M('data_phone')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, array());
        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $pattern = $this->getbk('phone');
        $caseList = M('data_phone')->where($where)->order('id asc')->limit($offset, $pagesize)->order('search_count desc')->select();
        foreach($caseList as $k => $c){
            if(!empty($pattern)){
                $caseList[$k]['keywords'] = preg_replace('/('.$pattern.')/is', '<del>$1</del>', $c['keywords']);
            }
        }

        $this->assign('k', $k);
        $this->assign('caseList', $caseList);
        $this->display();
    }

    public function pcase(){

        $k = I('get.k', '', 'trim');

        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize = 1000;
        $offset = ($page-1)*$pagesize;

        $where = array();
        if(!empty($k)){
            $where['keyword'] = array('like', '%'.$k.'%');
        }

        $count = M('data_case')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, array());
        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $pattern = $this->getbk('case');
        $caseList = M('data_case')->where($where)->order('id asc')->limit($offset, $pagesize)->select();
        foreach($caseList as $k => $c){
            if(!empty($pattern)){
                $caseList[$k]['keyword'] = preg_replace('/('.$pattern.')/is', '<del>$1</del>', $c['keyword']);
            }
        }

        $this->assign('k', $k);
        $this->assign('caseList', $caseList);
        $this->display();
    }

    public function watch(){
        $k = I('get.k', '', 'trim');
        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize = 1000;
        $offset = ($page-1)*$pagesize;

        $where = array();
        if(!empty($k)){
            $where['keyword'] = array('like', '%'.$k.'%');
        }

        $count = M('data_watch')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, array());
        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $pattern = $this->getbk('watch');
        $caseList = M('data_watch')->where($where)->order('id asc')->limit($offset, $pagesize)->select();
        foreach($caseList as $k => $c){
            if(!empty($pattern)){
                $caseList[$k]['keyword'] = preg_replace('/('.$pattern.')/is', '<del>$1</del>', $c['keyword']);
            }
        }

        $this->assign('caseList', $caseList);
        $this->display();
    }

    public function tshirt(){
        $k = I('get.k', '', 'trim');
        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize = 1000;
        $offset = ($page-1)*$pagesize;

        $where = array();
        if(!empty($k)){
            $where['keyword'] = array('like', '%'.$k.'%');
        }
        
        $count = M('data_tshirt')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, array());
        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $pattern = $this->getbk('tshirt');
        $caseList = M('data_tshirt')->where($where)->order('id asc')->limit($offset, $pagesize)->select();
        foreach($caseList as $k => $c){
            if(!empty($pattern)){
                $caseList[$k]['keyword'] = preg_replace('/('.$pattern.')/is', '<del>$1</del>', $c['keyword']);
            }
        }

        $this->assign('caseList', $caseList);
        $this->display();
    }



    public function a1(){

        $k = I('get.k', '', 'trim');

        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize = 1000;
        $offset = ($page-1)*$pagesize;

        $where = array();
        if(!empty($k)){
            $where['keywords'] = array('like', '%'.$k.'%');
        }

        $count = M('data_a1')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, array());
        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $caseList = M('data_a1')->where($where)->order('id asc')->limit($offset, $pagesize)->order('search_count desc')->select();

        $this->assign('k', $k);
        $this->assign('caseList', $caseList);
        $this->display();
    }




    public function a2(){

        $k = I('get.k', '', 'trim');

        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize = 1000;
        $offset = ($page-1)*$pagesize;

        $where = array();
        if(!empty($k)){
            $where['keywords'] = array('like', '%'.$k.'%');
        }

        $count = M('data_a2')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, array());
        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $caseList = M('data_a2')->where($where)->order('id asc')->limit($offset, $pagesize)->order('search_count desc')->select();

        $this->assign('k', $k);
        $this->assign('caseList', $caseList);
        $this->display();
    }




    public function a3(){

        $k = I('get.k', '', 'trim');

        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize = 1000;
        $offset = ($page-1)*$pagesize;

        $where = array();
        if(!empty($k)){
            $where['keywords'] = array('like', '%'.$k.'%');
        }

        $count = M('data_a3')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, array());
        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $caseList = M('data_a3')->where($where)->order('id asc')->limit($offset, $pagesize)->order('search_count desc')->select();

        $this->assign('k', $k);
        $this->assign('caseList', $caseList);
        $this->display();
    }



    public function a4(){

        $k = I('get.k', '', 'trim');

        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize = 1000;
        $offset = ($page-1)*$pagesize;

        $where = array();
        if(!empty($k)){
            $where['keywords'] = array('like', '%'.$k.'%');
        }

        $count = M('data_a4')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, array());
        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $caseList = M('data_a4')->where($where)->order('id asc')->limit($offset, $pagesize)->order('search_count desc')->select();

        $this->assign('k', $k);
        $this->assign('caseList', $caseList);
        $this->display();
    }
}
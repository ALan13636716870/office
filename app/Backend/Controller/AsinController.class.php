<?php
/**
 * Created by PhpStorm.
 * User: gaosk
 * Date: 16/2/3
 * Time: 14:29
 */
namespace Backend\Controller;

use Think\Exception;

class AsinController extends CommonController {
    private $marketArr = array(
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
    public function index(){

        $asin = I('get.asin', '', 'trim');
        $market = I('get.market', 0, 'intval');

        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize = 50;
        $offset = ($page-1)*$pagesize;

        $where = array();
        if(!empty($asin)){
            $where['asin'] = $asin;
        }
        if($market){
            $where['market_id'] = $market;
        }

        $count = M('im_asin')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, array());
        $pager->setConfig('theme','共 '.$count.' 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $asinList = M('im_asin')->where($where)->order('id asc')->limit($offset, $pagesize)->order('id desc')->select();

        $this->assign('market', $market);
        $this->assign('asin', $asin);
        $this->assign('asinList', $asinList);
        $this->assign('marketList', $this->marketArr);
        $this->display();
    }

    public function view($id){
        $info = M('im_asin')->where(array('id'=>$id))->find();
        $filePath = '/data/im_asin/'.$info['savename'];
        if(!file_exists($filePath)){
            exit('file error');
        }

        $content = file_get_contents($filePath);
        if(strpos($info['savename'], '.txt')){
            $content = str_replace('Distribution Fields', '', $content);
            $content = str_replace('History Fields', '', $content);
            $arr = preg_split('/\s*\n\s+\n\s*/', $content);
            $dataArr = array();
            foreach($arr as $k => $d){
                if(empty($d))continue;
                if($k == 0){
                    $dataArr['Basic'] = $d;
                }else{
                    preg_match('/(.*?)\s*\n/', $d, $match);
                    $key = str_replace(' ', '_', $match[1]);
                    if($key == 'Scalar_Fields' || $key == 'Alias')continue;
                    $dataArr[$key] = $d;
                }
            }
        }else{
            $_conArr = json_decode($content, true);
            $conArr = $_conArr['productQueryResponse']['productQueryResult']['results'][0]['value'];
            $dataArr = array();
            foreach($conArr['DistributionFields'] as $od){
                $dataArr[$od['name']] = $this->arr2str($od['value']);
            }
            foreach($conArr['HistoryFields'] as $od){
                $dataArr[$od['name']] = $this->arr2str($od['value']);
            }
        }

        $this->assign('asin', $info['asin']);
        $this->assign('dataArr', $dataArr);
        $this->display();
    }

    private function arr2str($arr){
        $strArr = array();
        foreach($arr as $data){
            $str = '';
            foreach($data as $d){
                $str .= "\t".$d;
            }
            $strArr[] = $str;
        }
        return implode("<br />", $strArr);
    }

    public function upload(){
        $fileSavePath = '/data/im_asin/';
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->rootPath  =     $fileSavePath; // 设置附件上传根目录
        $upload->savePath  =     '';
        $upload->autoSub   =     '';
        $upload->subName   =     '';
        $upload->saveName  =     '';

        // 上传文件 
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->ajaxReturn(array('status'=>'0', 'msg'=>$upload->getError()));
        }else{// 上传成功
            $fileInfo = $info['file'];

            //上传txt不带后缀的文件
            if($fileInfo['ext'] == ''){
                $filePath = $fileSavePath.$fileInfo['savename'];
                $content = file_get_contents($filePath);
                preg_match('/ASIN: ([a-zA-Z0-9]+)[\s]+/', $content, $match);
                $asin = $match[1];
                preg_match('/Market: ([a-zA-Z0-9]+)[\s\n\r]+/', $content, $match);
                $market = $match[1];
                if(empty($market))$market='US';
                $savename = $asin.'('.$market.').txt';

                //重新命名文件
                $newFilePath = $fileSavePath.$savename;
                rename($filePath, $newFilePath);

            }else{
                $filePath = $fileSavePath.$fileInfo['savename'];
                $content = file_get_contents($filePath);
                $conArr = json_decode($content, true);
                $asin = $conArr['productQueryResponse']['productQueryResult']['results'][0]['key']['asin'];
                $marketId = $conArr['productQueryResponse']['productQueryResult']['results'][0]['key']['mpid'];
                $market = $this->marketArr[$marketId];
                if(empty($market))$market='US';
                $savename = $fileInfo['savename'];
            }

            $data = array(
                'asin'=>$asin,
                'savename'=>$savename,
                'market'=>$market
            );
            M('im_asin')->add($data);
            $this->ajaxReturn(array('status'=>'1', 'msg'=>$data));
        }
    }

}
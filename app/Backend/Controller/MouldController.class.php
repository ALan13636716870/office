<?php
/**
 * Created by PhpStorm.
 * User: adminditor
 * Date: 2018/1/25
 * Time: 11:06
 */

namespace Backend\Controller;

use Think\Controller;
use Think\Model;
class Mouldcontroller extends Controller
{
    public function index(){
        $id = I('get.id',0,'htmlspecialchars,trim');

        $sideList = M('mould_side')->where(array('cat_id'=>$id))->select();

        $this ->assign('cat_id',$id);
        $this -> assign('sideList',$sideList);
        $this -> display();
    }
    public function edit($id = 0){
        if(!IS_POST){
            $cat_id = I('get.cat_id',0,'intval');
            if(!$cat_id){
                $this->error('必须指定模具');
            }

            $mouldInfo = M('category')->where(array('cat_id'=>$cat_id))->find();
            if(!$mouldInfo){
                $this->error('模具不存在');
            }

            if($id){
                $sideInfo = M('mould_side')->where(array('id' => $id))->find();
                if(!$sideInfo){
                    $this->error('模具面不存在');
                }
            }else{
                $sideInfo = array('id'=>0);
            }

            $this->assign('cat_id', $cat_id);
            $this->assign('sideInfo', $sideInfo);
            $this->display();
        }else{
            $catId = I('post.cat_id',0,'intval');
            if(!$catId){
                $this->error('必须指定模具');
            }

            $mouldInfo = M('category')->where(array('cat_id'=>$catId))->find();
            if(!$mouldInfo){
                $this->error('模具不存在');
            }

            $title = I('post.title', '', 'trim');
            if(empty($title)){
                $this->error('模具面名称不能为空');
            }

            $createPhoto = I('post.create_photo', '', 'trim');
            $designPhoto = I('post.design_photo', '', 'trim');

            /*$photoes = I('post.photoes');
            $newPhotoes = array();
            if(is_array($photoes) and count($photoes)){
                foreach($photoes as $photo => $order){
                    if(array_key_exists($order, $newPhotoes)){
                        $newPhotoes[]= $photo;
                    }else{
                        $newPhotoes[$order] = $photo;
                    }
                }
                ksort($newPhotoes);
            }*/
            $data = array(
                'title' => $title,
                'width' => I('post.width', '', 'intval'),
                'height' => I('post.height', '', 'intval'),
                'mould_width' => I('post.mould_width', '', 'intval'),
                'mould_height' => I('post.mould_height', '', 'intval'),
                'cat_id' => $catId,
                'create_photo' => $createPhoto,
                'design_photo' => $designPhoto,
                'position' => I('post.position', '', 'intval'),
                'display_order' => I('post.display_order', '', 'intval'),
                'coordinate_start' => I('post.coordinate_start', '', 'trim'),
                'coordinate_end' => I('post.coordinate_end', '', 'trim'),
                'need_create' => I('post.need_create',1,'intval')
            );

            if($data['need_create']){
                if(!preg_match('/^([\d\-]+),([\d\-]+)$/is', $data['coordinate_start'])){
                    $this->error('起始坐标填错了');
                }
                if(!preg_match('/^([\d\-]+),([\d\-]+)$/is', $data['coordinate_end'])){
                    $this->error('结束坐标填错了');
                }
            }

            if($id){
                $sideInfo = M('mould_side')->where(array('id'=>$id))->find();
                if(!$sideInfo){
                    $this->error('模具面不存在');
                }

                M('mould_side')->where(array('id'=>$id))->save($data);

                if($sideInfo['width'] != $data['width'] || $sideInfo['height'] != $data['height']
                    || $sideInfo['mould_width'] != $data['mould_width'] || $sideInfo['mould_height'] != $data['mould_height']
                    || $sideInfo['need_create'] != $data['need_create']
                ){
                    M('product')->where(array('side_id'=>$id))->delete();
                }
            }else{
                $id = M('mould_side')->add($data);
            }

            //上传不需要生成的图片到服务器
            if($data['need_create'] == '0'){
                $shellStr = 'cd '.C('OSSCMD_PATH').' ';
                $sessionOssBucket = session('company.oss_bucket');
                $ossBucket = empty($sessionOssBucket) ? C('OSS_PRODUCT_BUCKET') : $sessionOssBucket;
            }

            //下载图片
            //格式: mould/模具ID/面ID_格式化的图片路径
            $localPath = ROOT_PATH.'data/mould/'.$mouldId.'/';
            //rmdirr($localPath);
            @mkdir($localPath,0777,true);
            foreach($newPhotoes as $photo){
                $remoteFile = C('OSS_IMG_HOST') . '/' . $photo;
                $localFile = $localPath.$id.'_'.str_replace('/','_',$photo);
                $img = file_get_contents($remoteFile);
                file_put_contents($localFile, $img);

                if($data['need_create'] == '0'){
                    $ext = substr(strrchr($photo, '.'), 1);
                    $shellStr .= '&& python osscmd put '.$localFile.' oss://'.$ossBucket.'/'.$mouldId.'/'.$id.'.'.$ext;
                    $shellStr .= '&& rm -rf '.$localFile.' ';
                }
            }

            if($data['need_create'] == '0'){
                $shellPath = AMZP_PATH.AMZP_TEMP . '/mould_'.date('YmdHis').'_'.session('account.id').'.sh';
                $shellStr .= '&& rm -rf '.$shellPath;
                file_put_contents($shellPath, $shellStr);
                exec('sh '.$shellPath.' >/dev/null &');
            }

            $this->success('模具面编辑成功！', U('mould/index', array('id'=>$data['cat_id'])));
        }

    }
    public function add(){
        if(IS_POST){
            $title = I('post.title', '', 'trim');
            if(empty($title)){
                $this->error('模具面名称不能为空');
            }
            $catId = I('post.cat_id', '', 'trim');
            $createPhoto = I('post.create_photo', '', 'trim');
            $designPhoto = I('post.design_photo', '', 'trim');
            $data = array(
                'title' => $title,
                'width' => I('post.width', '', 'intval'),
                'height' => I('post.height', '', 'intval'),
                'mould_width' => I('post.mould_width', '', 'intval'),
                'mould_height' => I('post.mould_height', '', 'intval'),
                'cat_id' => $catId,
                'create_photo' => $createPhoto,
                'design_photo' => $designPhoto,
                'position' => I('post.position', '', 'intval'),
                'display_order' => I('post.display_order', '', 'intval'),
                'coordinate_start' => I('post.coordinate_start', '', 'trim'),
                'coordinate_end' => I('post.coordinate_end', '', 'trim'),
                'need_create' => I('post.need_create',1,'intval')
            );

            if($data['need_create']){
                if(!preg_match('/^([\d\-]+),([\d\-]+)$/is', $data['coordinate_start'])){
                    $this->error('起始坐标填错了');
                }
                if(!preg_match('/^([\d\-]+),([\d\-]+)$/is', $data['coordinate_end'])){
                    $this->error('结束坐标填错了');
                }
            }
            $id = M('mould_side')->add($data);

            //上传不需要生成的图片到服务器
            if($data['need_create'] == '0'){
                $shellStr = 'cd '.C('OSSCMD_PATH').' ';
                $sessionOssBucket = session('company.oss_bucket');
                $ossBucket = empty($sessionOssBucket) ? C('OSS_PRODUCT_BUCKET') : $sessionOssBucket;
            }

            //下载图片
            //格式: mould/模具ID/面ID_格式化的图片路径
            $localPath = ROOT_PATH.'data/mould/'.$catId.'/';
            //rmdirr($localPath);
            @mkdir($localPath,0777,true);
            foreach($newPhotoes as $photo){
                $remoteFile = C('OSS_IMG_HOST') . '/' . $photo;
                $localFile = $localPath.$id.'_'.str_replace('/','_',$photo);
                $img = file_get_contents($remoteFile);
                file_put_contents($localFile, $img);

                if($data['need_create'] == '0'){
                    $ext = substr(strrchr($photo, '.'), 1);
                    $shellStr .= '&& python osscmd put '.$localFile.' oss://'.$ossBucket.'/'.$catId.'/'.$id.'.'.$ext;
                    $shellStr .= '&& rm -rf '.$localFile.' ';
                }
            }

            if($data['need_create'] == '0'){
                $shellPath = AMZP_PATH.AMZP_TEMP . '/mould_'.date('YmdHis').'_'.session('account.id').'.sh';
                $shellStr .= '&& rm -rf '.$shellPath;
                file_put_contents($shellPath, $shellStr);
                exec('sh '.$shellPath.' >/dev/null &');
            }

            $this->success('模具面编辑成功！', U('mould/index', array('id'=>$data['cat_id'])));
        }else{
            $catId = I('post.cat_id', '', 'trim');
            $this -> assign('cat_id',$catId);
            $this ->display('edit');
        }
    }
}
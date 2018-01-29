<?php
/**
 * Created by PhpStorm.
 * User: gaosk
 * Date: 16/2/18
 * Time: 18:03
 */
namespace Backend\Controller;

class BatchController extends CommonController {

    public function index(){
        $batchList = M('product_batch')->where(array('company_id'=>session('account.company_id')))
            ->order('add_time desc')->select();

        $this->assign('batchList', $batchList);
        $this->display();
    }

    public function edit(){
        if(IS_GET){
            $batchId = I('get.batch_id', 0, 'intval');
            if($batchId){
                $batchInfo = M('product_batch')->where(array('id' => $batchId))->find();
                if(!$batchInfo){
                    $this->dialogError('批次不存在');
                }
                $marr = explode(',', $batchInfo['mould_ids']);
                $batchInfo['mould_ids'] = json_encode($marr);
            }else{
                $batchInfo = array('id'=>0,'is_active'=>0,'merge_topic'=>0,'mould_ids'=>json_encode(array()));
            }

            $categoryData = M('category')->where(array('company_id'=>$this->companyId))->select();
            $categoryList = getSubs($categoryData);
            foreach($categoryList as $k => $c){
                $categoryList[$k]['mould_list'] = M('mould')->where(array('category_id'=>$c['id'],'status'=>1))->select();
            }

            $this->assign('categoryList', $categoryList);
            $this->assign('batchId', $batchId);
            $this->assign('batchInfo', $batchInfo);
            $this->display();
        }else{
            $batchId = I('post.batch_id', 0, 'intval');
            $batchNo = I('post.batch_no', '', 'trim');
            $brandName = I('post.brand_name', '', 'trim');
            $productName = I('post.product_name', '', 'trim');
            $titleTemplate = I('post.title_template', '', 'trim');
            $descTemplate = I('post.description_template', '', 'trim');
            $introTemplate = I('post.introduce_template', '', 'trim');
            $kt1 = I('post.keyword_template_1', '', 'trim');
            $kt2 = I('post.keyword_template_2', '', 'trim');
            $kt3 = I('post.keyword_template_3', '', 'trim');
            $kt4 = I('post.keyword_template_4', '', 'trim');
            $kt5 = I('post.keyword_template_5', '', 'trim');
            $kt6 = I('post.keyword_template_6', '', 'trim');
            $kt7 = I('post.keyword_template_7', '', 'trim');
            $kt8 = I('post.keyword_template_8', '', 'trim');
            $modifier = I('post.modifier', '', 'trim');
            $isActive = I('post.is_active', '', 'trim');
            $isPublish = I('post.is_publish', '', 'intval');
            $mergeTopic = I('post.merge_topic', '', 'trim');
            $categoryId = I('post.category_id', '', 'intval');
            if(empty($batchNo)){
                $this->error('批次名称不能为空');
            }

            //类别
            $mouldArr = I('post.mould_id', array());
            $mouldIds = implode(',', $mouldArr);


            $data = array(
                'batch_no' => $batchNo,
                'brand_name' => $brandName,
                'is_active' => $isActive,
                'is_publish' => $isPublish,
                'product_name' => $productName,
                'modifier' => $modifier,
                'category_id' => $categoryId,
                'mould_ids' => $mouldIds,
                'merge_topic' => $mergeTopic,
                'title_template' => $titleTemplate,
                'description_template' => $descTemplate,
                'introduce_template' => $introTemplate,
                'keyword_template_1' => $kt1,
                'keyword_template_2' => $kt2,
                'keyword_template_3' => $kt3,
                'keyword_template_4' => $kt4,
                'keyword_template_5' => $kt5,
                'keyword_template_6' => $kt6,
                'keyword_template_7' => $kt7,
                'keyword_template_8' => $kt8
            );

            if($batchId){
                //权限检测
                $batchInfo = M('product_batch')->where(array('id' => $batchId))->find();
                if(!$batchInfo){
                    $this->error('批次不存在');
                }
                //只能修改自己的
                if($batchInfo['user_id'] != session('account.id')) {
                    $this->error('无法修改此批次');
                }

                M('product_batch')->where(array('id'=>$batchId))->save($data);
            }else{
                $data['add_time'] = time();
                $data['user_id'] = session('account.id');
                $data['company_id'] = $this->companyId;
                $batchId = M('product_batch')->add($data);
            }

            if($isActive){
                M('product_batch')->where(array(
                    'id' => array('NEQ', $batchId),
                    'user_id' => session('account.id')
                ))->save(array('is_active'=>0));
            }
            $this->success('批次编辑成功', U('batch/index'));
        }
    }

    public function delete(){
        $batchId = I('get.batch_id', 0, 'intval');
        $batchInfo = M('product_batch')->where(array('id' => $batchId))->find();
        if(!$batchInfo){
            $this->error('批次不存在');
        }
        if($batchInfo['user_id'] != session('account.id')){
            $this->error('无法删除批次');
        }
        if($batchInfo['is_publish']){
            $this->error('无法删除批次');
        }
        M('product_batch')->where('id='.$batchId)->delete();
        $this->success('批次删除成功', U('batch/index'));
    }
}

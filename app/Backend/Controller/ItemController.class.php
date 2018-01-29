<?php
/**
 * Created by PhpStorm.
 * User: gaosk
 * Date: 16/2/3
 * Time: 14:29
 */
namespace Backend\Controller;

use Think\Exception;

class ItemController extends CommonController {
    private $basicFields = array(
        'item_type',
        'item_sku',
        'external_product_id',
        'external_product_id_type',
        'item_name',
        'manufacturer',
        'brand_name',
        'standard_price',
        'quantity',
        'other_image_url1',
        'other_image_url2',
        'other_image_url3',
        'other_image_url4',
        'other_image_url5',
        'other_image_url6',
        'other_image_url7',
        'other_image_url8',
        'main_image_url',
        'swatch_image_url',
        'parent_child',
        'parent_sku',
        'relationship_type',
        'variation_theme',
        'part_number',
        'product_description',
        'update_delete',
        'bullet_point1',
        'bullet_point2',
        'bullet_point3',
        'bullet_point4',
        'bullet_point5',
        'generic_keywords1',
        'generic_keywords2',
        'generic_keywords3',
        'generic_keywords4',
        'generic_keywords5',
        'color_name',
        'color_map',
        'compatible_phone_models1',
        'compatible_phone_models2',
        'compatible_phone_models3',
        'compatible_phone_models4',
        'compatible_phone_models5',
        'compatible_phone_models6',
        'subject_keywords1',
        'subject_keywords2',
        'subject_keywords3',
        'subject_keywords4',
        'subject_keywords5',
        'sale_price',
        'sale_from_date',
        'sale_end_date',
        'condition_type',
        'condition_note',
        'fulfillment_latency',
        'asin'
    );

    public function index(){
        $parent = I('get.parent',0,'trim');
        $all = I('get.all',0,'intval'); //是否显示所有商品，不分子父商品

        //用户列表
        $userList = M('users')->where(array('company_id'=>$this->companyId))->field('id,alias_name')->select();
        $userKeyArr = array_column($userList, 'id');
        $userValArr = array_column($userList, 'alias_name');
        $userList = array_combine($userKeyArr, $userValArr);

        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        if($topicId)$pagesize = 10000;
        else $pagesize=30;
        $offset = ($page-1)*$pagesize;

        //搜索条件
        $urlParams = array();
        $orderArr = array();
        $where = array('company_id'=>$this->companyId);
        if(!empty($parent)){
            $where['parent_sku'] = $parent;
            $orderArr['id'] = 'desc';
        }else{
            //排序
            if($all){
                $orderArr['id'] = 'desc';
            }else{
                $orderArr['id'] = 'desc';
                $where['parent_sku'] = '';
            }
        }


        $count = M('item')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize, $urlParams);
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $itemList = M('item')->where($where)
            ->field('id,item_type,item_sku,item_name,main_image_url,parent_child,parent_sku,variation_theme,'.
                'color_name,user_id,company_id,add_time,update_history,remark,standard_price,quantity,external_product_id,asin')
            ->order($orderArr)->limit($offset, $pagesize)->select();

        foreach($itemList as $k => $v){
            $itemList[$k]['user_name'] = $userList[$v['user_id']];
            if($v['parent_child'] == 'Parent'){
                $itemList[$k]['child_num'] = M('item')->where(array('parent_sku'=>$v['item_sku']))->count();
            }
        }

        $this->assign('parent', $parent);
        $this->assign('itemList', $itemList);


        $templateList = M('template')->where(array(
            'company_id' => $this->companyId
        ))->select();
        $this->assign('templateList', $templateList);

        if(!empty($parent)){
            $this->display('Item/list');
        }else{
            $this->display();
        }
    }

    public function edit(){
        if(IS_GET){
            $id = I('get.id', '', 'intval');
            $parent = I('get.parent', '', 'intval');
            $copy = I('get.copy', '', 'intval');

            if($id){
                $itemInfo = M('item')->where(array('id'=>$id,'company_id'=>$this->companyId))->find();
                if(!$itemInfo){
                    $this->error("指定的商品不存在");
                }
            }else if($parent){
                $itemInfo = M('item')->where(array('id'=>$parent,'company_id'=>$this->companyId))->find();
                if(!$itemInfo){
                    $this->error("指定的父商品不存在");
                }
                $itemInfo['parent_sku'] = $itemInfo['item_sku'];
                $itemInfo['parent_child'] = 'Child';
                $itemInfo['relationship_type'] = 'Variation';
                $itemInfo['item_sku'] = '';
                $itemInfo['id'] = 0;
            }else if($copy){
                $itemInfo = M('item')->where(array('id'=>$copy,'company_id'=>$this->companyId))->find();
                if(!$itemInfo){
                    $this->error("指定拷贝的商品不存在");
                }
                $itemInfo['item_sku'] = '';
                $itemInfo['id'] = 0;
            }else{
                //默认值
                $itemInfo = array(
                    'id' => 0,
                    'external_product_id_type' => 'UPC',
                    'condition_type' => 'New',
                    'fulfillment_latency' => 4
                );
            }

            $this->assign('itemInfo', $itemInfo);
            $this->display();
        }else{
            $data = array();
            foreach($this->basicFields as $field){
                $data[$field] = I('post.'.$field, '', 'trim');
            }
            if($data['quantity'] == '')$data['quantity'] = 0;
            $data['remark'] = I('post.remark', '', 'trim');

            //一些数据的修正
            if($data['parent_child'] == 'Parent'){
                $data['external_product_id'] = '';
                $data['external_product_id_type'] = '';
                $data['parent_sku'] = '';
                $data['relationship_type'] = '';
            }

            //修改或保存
            $itemId = I('post.id', 0, 'intval');
            if($itemId){
                //权限检测
                $itemInfo = M('item')->where(array('id' => $itemId,'company_id'=>$this->companyId))->field('item_sku,update_history,parent_sku,parent_child')->find();
                if(!$itemInfo){
                    $this->error('商品不存在');
                }
                //修改商品不允许修改SKU
                $data['item_sku'] = $itemInfo['item_sku'];
                //更新修改历史
                $data['update_history'] =  session('account.id')."\t".time()."\n".$itemInfo['update_history'];

                //由父商品改为其它类型
                if($itemInfo['parent_child'] == 'Parent' && $data['parent_child'] != 'Parent'){
                    $subNum = M('item')->where(array('parent_sku' => $itemInfo['item_sku'],'company_id'=>$this->companyId))->count();
                    if($subNum){
                        $this->error("父商品里包含有子商品，不能修改子父类型");
                    }
                }
                //由其它类型商品改为父商品
                else if($itemInfo['parent_child'] != 'Parent' && $data['parent_child'] == 'Parent'){    
                    $data['external_product_id'] = '';
                    $data['external_product_id_type'] = '';
                    $data['parent_sku'] = '';
                    $data['relationship_type'] = '';
                }

                M('item')->where(array('id'=>$itemId,'company_id'=>$this->companyId))->save($data);
            }else{
                $data['add_time'] = time();
                $data['company_id'] = $this->companyId;
                $data['user_id'] = session('account.id');
                M('item')->add($data);
            }
            $this->success('商品编辑成功', U('item/index'));
        }
    }

    public function delete($id){
        //权限检测
        $itemInfo = M('item')->where(array('id' => $id,'company_id'=>$this->companyId))->find();
        if(!$itemInfo){
            $this->error('产品不存在');
        }

        if($itemInfo['parent_child'] == 'Parent'){
            $subCount = M('item')->where(array('parent_sku' => $itemInfo['item_sku'],'company_id'=>$this->companyId))->count();
            if($subCount){
                $this->error('还有子产品，无法删除');
            }
        }
        M('item')->where(array('id' => $id))->delete();
        $this->success('产品删除成功', U('item/index'));
    }


    //导出产品
    public function export($item_ids, $template_id){
        set_time_limit(0);

        $templateInfo = M('template')->where(array(
            'company_id' => $this->companyId,
            'id' => $template_id
        ))->find();
        if(!$templateInfo)exit('template not exits!');

        $fieldList = M('template_field')->where(array('template_id'=>$template_id))->field('title,value')->order('display_order asc')->select();


        $itemIdArr = array_filter(array_unique(explode(',', $item_ids)));
        if(empty($itemIdArr))exit('item ids empty!');
        $itemList = M('item')->where(array('id'=>array('in', implode(',', $itemIdArr))))->select();

        //导出表头
        $content = '';
        foreach($fieldList as $field){
            $content .= $field['title']."\t";
        }
        $content .= "\n";

        //导出商品列表
        foreach($itemList as $item){
            foreach($fieldList as $field){
                $value = $item[$field['title']];
                if(empty($value)){
                    $value = $field['value'];
                }
                $value = nl2br(trim($value));
                $value = preg_replace("/\r/", "", $value);

                if(!empty($value) && 
                    in_array($field['title'], array('main_image_url','other_image_url1','other_image_url2','other_image_url3',
                    'other_image_url4','other_image_url5','other_image_url6'))
                ){
                    $value = C('OSS_IMG_HOST').'/'.$value;
                }

                $content .= $value."\t";
            }
            $content .= "\n";
        }

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment;filename="item_export.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public');


        exit($content);
    }


}
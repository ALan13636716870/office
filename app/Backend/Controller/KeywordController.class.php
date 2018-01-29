<?php
namespace Backend\Controller;

class KeywordController extends CommonController{

    public function index(){
        $name = I('get.name', '', 'trim');
        $status = I('get.status', '', 'trim');
        $where = array();

        if(!empty($name)){
            $where['_string'] = "(name like '%".$name."%' or keywords like '%".$name."%' or chinese like '%".$name."%')";
        }
        if($status!=''){
            $where['status'] = $status;
        }

        //分页
        $page = I('get.page', 0, 'intval');
        $page = max(1, $page);
        $pagesize=30;
        $offset = ($page-1)*$pagesize;

        $count = M('keyword')->where($where)->count();
        $pager = new \Think\Page($count, $pagesize);
        $pager->setConfig('theme','共 %TOTAL_ROW% 条记录, %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $pageStr = $pager->show();
        $this->assign('pageStr', $pageStr);

        $keywordList = M('keyword')
            ->where($where)->limit($offset, $pagesize)
            ->order('id desc')->select();

        $this->assign('name', $name);
        $this->assign('status', $status);
        $this->assign('keywordList', $keywordList);
        $this->display();
    }

    public function edit($id){
        if(IS_POST){
            $name = I('post.name', '', 'trim');
            $keywords = I('post.keywords', '', 'trim');
            $chinese = I('post.chinese', '', 'trim');
            $status = I('post.status', '', 'trim');
            $data = array(
                'name' => $name,
                'keywords' => $keywords,
                'chinese' => $chinese,
                'status' => $status
            );
            if($id){
                M('keyword')->where(array('id'=>$id))->save($data);
            }else{
                M('keyword')->add($data);
            }
            $this->success('关键词编辑成功', U('keyword/index'));
        }else{
            if(!empty($id)){
                $keywordInfo = M('keyword')->where(array('id'=>$id))->find();
            }else{
                $keywordInfo = array('id'=>0,'status'=>0);
            }

            $this->assign('keywordInfo', $keywordInfo);
            $this->display();
        }
    }

    public function filter(){
    	$_keywordList = M('keyword')->where(array('status'=>-1))->order('id desc')->field('id,name,keywords')->select();
    	$keywordList = array();
    	foreach($_keywordList as $k => $v){
    		$wordList = preg_split('/([\n\r]+)/', trim($v['keywords']));
    		$wordList[] = $v['name'];
    		$wordList = array_unique(array_filter($wordList));
    		$keywordList[$v['id']] = $wordList;
    	}

    	$this->assign('keywordList', $keywordList);
    	$this->display();
    }

    public function delete($id){
        M('keyword')->where(array('id'=>$id))->delete();
            $this->success('关键词删除成功', U('keyword/index'));
    }
}
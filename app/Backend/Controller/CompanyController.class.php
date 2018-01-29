<?php
/**
 * Created by PhpStorm.
 * User: gaosk
 * Date: 16/3/31
 * Time: 15:45
 */
namespace Backend\Controller;

class CompanyController extends CommonController {
    public function __construct(){
        parent::__construct();
        if($this->identity != 'superadmin'){
            exit('permission denied!');
        }
    }
    public function index(){
        $companyList = M('company')
            ->join('left join __USER__ ON __USER__.id = __COMPANY__.admin_id')
            ->field('company.*,user.username,user.alias_name'
            )->select();

        $this->assign('companyList', $companyList);
        $this->display();
    }

    public function edit($id = 0){
        if(!IS_POST){
            if($id){
                $companyInfo = M('company')->where(array('id' => $id))->find();
                if(!$companyInfo){
                    $this->error('企业不存在', U('company/index'));
                }
                $adminInfo = M('user')->where(array('id'=>$companyInfo['admin_id']))->find();
            }else{
                $companyInfo = array('id'=>0);
                $adminInfo = array('id'=>0);
            }

            $this->assign('adminInfo', $adminInfo);
            $this->assign('companyInfo', $companyInfo);
            $this->display();
        }else{
            $companyInfo = I('post.company');
            $userInfo = I('post.user');
            $userInfo['identity'] = 'administrator';
            $userInfo['status'] = '1';
            if($userInfo['password'] != ''){
                $userInfo['password'] = password_hash($userInfo['password'], PASSWORD_DEFAULT);
            }else{
                unset($userInfo['password']);
            }

            if(empty($companyInfo['name'])){
                $this->error('企业名称不能为空');
            }
            if(empty($userInfo['username'])){
                $this->error('企业管理员登录名不能为空');
            }
            if(empty($userInfo['alias_name'])){
                $this->error('企业管理员别名不能为空');
            }

            if($id){
                $companyData = M('company')->where(array('id' => $id))->find();
                if(!$companyData){
                    $this->error('企业信息不存在');
                }
                $userId = $companyData['admin_id'];
                $userAdmin = M('user')->where(array('id'=>$userId))->find();
                if(!$userAdmin){
                    $userId = M('user')->add($userInfo);
                    if(!$userId){
                        $this->error('用户添加错误');
                    }
                    $companyInfo['admin_id'] = $userId;
                }else{
                    $userData = M('user')->where(array('id' => $userId))->find();
                    if($userData['identity'] == 'superadmin'){
                        $userInfo['identity'] = 'superadmin';
                    }
                    M('user')->where(array('id'=>$userId))->save($userInfo);
                }
                M('company')->where(array('id'=>$id))->save($companyInfo);
                M('user')->where(array('id'=>$userId))->save(array('company_id'=>$id));
            }else{
                $userId = M('user')->add($userInfo);
                $companyInfo['admin_id'] = $userId;
                $companyId = M('company')->add($companyInfo);
                M('user')->where(array('id'=>$userId))->save(array('company_id'=>$companyId));
            }

            $this->success('企业编辑成功！', U('company/index'));
        }
    }

}

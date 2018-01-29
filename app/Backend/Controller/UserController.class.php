<?php
/**
 * Created by PhpStorm.
 * User: gaosk
 * Date: 16/3/31
 * Time: 15:45
 */
namespace Backend\Controller;

class UserController extends CommonController {
    public function __construct(){
        parent::__construct();
        if($this->identity != 'superadmin' && $this->identity != 'administrator'){
            exit('permission denied!');
        }
    }
    public function index(){
        $userList = M('user')->where(array('company_id'=>$this->companyId))->select();

        $this->assign('userList', $userList);
        $this->display();
    }

    public function edit($id = 0){
        if(!IS_POST){
            if($id){
                $userInfo = M('user')->where(array('id' => $id,'company_id'=>$this->companyId))->find();
                if(!$userInfo){
                    $this->error('用户不存在', U('user/index'));
                }
            }else{
                $userInfo = array('id'=>0);
            }

            $this->assign('userInfo', $userInfo);
            $this->display();
        }else{
            $username = I('post.username','','trim');
            $aliasName = I('post.alias_name','','trim');
            $password = I('post.password');
            $identity = I('post.identity','','trim');
            $status = I('post.status','','intval');
            $excelFull = I('post.excel_full','','intval');

            if(empty($username) || empty($aliasName)){
                $this->error('用户名和用户别名不能为空');
            }

            $userInfo = array(
                'username' => $username,
                'alias_name' => $aliasName,
                'status' => $status,
                'excel_full' => $excelFull,
                'company_id' => $this->companyId
            );
            if($password != ''){
                $userInfo['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            if($id){
                $userData = M('user')->where(array('id' => $id))->find();
                if(!$userData){
                    $this->error('用户信息不存在');
                }
                if($userData['identity'] != 'superadmin'){
                    $userInfo['identity'] = $identity;
                    if($userInfo['identity'] == 'superadmin'){
                        $userInfo['identity'] = $userData['identity'];
                    }
                }
                M('user')->where(array('id'=>$id))->save($userInfo);
            }else{
                $userInfo['identity'] = $identity;
                if($userInfo['identity'] == 'superadmin'){
                    $userInfo['identity'] = 'user';
                }
                M('user')->add($userInfo);
            }

            $this->success('用户编辑成功！', U('user/index'));
        }
    }

}

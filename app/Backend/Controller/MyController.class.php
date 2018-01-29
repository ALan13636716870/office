<?php
/**
 * Created by PhpStorm.
 * User: gaosk
 * Date: 16/2/16
 * Time: 16:19
 */
namespace Backend\Controller;

class MyController extends CommonController {

    //基本信息修改
    public function profile(){
        if(IS_GET){
            $account = M('user')->where('id='.session('account.id'))->find();

            if(!$account){
                $this->dialogError('','','未获取到个人资料');
            }
            $this->assign('account', $account);
            $this->display();
        }else{
            $name = I('post.alias_name','','trim');
            if(empty($name)){
                $this->dialogError('用户别名不能为空');
            }
            $userData = array(
                'alias_name' => $name
            );
            $password = I('post.password');
            if($password != ''){
                $userData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            M('user')->where('id='.session('account.id'))->save($userData);
            $this->dialogSuccess('','','个人资料修改成功');
        }
    }

}

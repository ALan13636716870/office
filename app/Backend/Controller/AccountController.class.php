<?php
/**
 * Created by PhpStorm.
 * User: gaosk
 * Date: 15/12/17
 * Time: 16:56
 */

namespace Backend\Controller;
use Think\Controller;

class AccountController extends Controller {

    /*
     * 登录表单
     */
    public function signin(){
        
        if(IS_GET){
            if(session('account')){
                $this->redirect(U(session('account.identity').'/index/index'));
            }
            $this->display();
        }else{
            $this->doSign();
        }
    }

    private function doSign(){
        $username = I('post.office_username','','htmlspecialchars,trim');
        $password = I('post.office_password');

        $account = M('admin')->where(array('username'=>$username))->find();
        if(empty($account)){
            $this->error('账号不存在');
        }

//        if(!$account['status']){
//            $this->error('账号已禁用');
//        }
        if(!password_verify($password, $account['password'])){
            $this->error('密码错误');
        }

//        $companyInfo = M('company')->where(array('id'=>$account['company_id']))->find();
//        if(!$companyInfo['status'])exit('Account Error, Please try later!');

        session('account', $account);
        session('company', $companyInfo);

        $this->success('登录成功', U('index/index'));
    }

    /*
     * 登录验证码获取
     */
    public function verify(){
        $config = array(
            'fontSize' => 15,
            'length' => 4,
            'imageW' => 100,
            'imageH' => 34,
            'bg' => array(22, 150, 215),
            'useCurve' => false,
            'useNoise' => false
        );
        $verify = new \Think\Verify($config);
        $verify->entry();
    }

    /*
     * 退出登录
     */
    public function logout(){
        session('account', null);
        $this->success('已退出登录', U('index/index'));
    }

}
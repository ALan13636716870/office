<?php
namespace Backend\Controller;
use Think\Controller;

class CommonController extends Controller {
    protected $account = array();
    protected $companyId = 0;
    protected $identity = 'user';

    public function __construct(){
        parent::__construct();

        $account = session('account');
        if(!$account){
            $this->error('还未登录', U('account/signin'));
        }

        //获取用户信息
        $this->account = $account;
        $this->companyId = $account['company_id'];
        $this->identity = $account['identity'];
        $this->assign('account', $account);
        $this->assign('identity', $account['identity']);
    }

    protected function checkManager(){
        if(session('account.identity') == 'user'){
            return false;
        }
        return true;
    }

    public function dialogSuccess($callback = '', $callbackData = array(), $msg = '', $timeout = 2){
        $timeout *= 1000;
        $this->assign('callback', $callback);
        $this->assign('callback_data', json_encode($callbackData));
        $this->assign('timeout', $timeout);
        $this->assign('msg', $msg);
        $this->display('Common/dialog_success');
        exit;
    }

    public function dialogError($msg, $timeout = 5){
        $timeout *= 1000;
        $this->assign('msg', $msg);
        $this->assign('timeout', $timeout);
        $this->display('Common/dialog_error');
        exit;
    }

}
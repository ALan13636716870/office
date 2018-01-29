<?php
/**
 * Created by PhpStorm.
 * User: adminditor
 * Date: 2018/1/27
 * Time: 15:29
 */

namespace Home\Model;
use Think\Think;
use Think\Model;

class PaypalModel
{
    private $_form = '';
    private $_user = '';
    private $_from = '';
    private $_url = '';
    private $account = '';

    private function put(){
//        $def_url  = '<br /><form style="text-align:center;" action="'.$this ->_url.'" method="post" target="_blank">' .   // 不能省略
//            "<input type='hidden' name='cmd' value='_xclick'>" .                             // 不能省略
//            "<input type='hidden' name='business' value='".$this -> account."'>" .                 // 贝宝帐号
//            "<input type='hidden' name='item_name' value='$order[order_sn]'>" .                 // payment for
//            "<input type='hidden' name='amount' value='$data_amount'>" .                        // 订单金额
//            "<input type='hidden' name='currency_code' value='$currency_code'>" .            // 货币
//            "<input type='hidden' name='return' value='$data_return_url'>" .                    // 付款后页面
//            "<input type='hidden' name='invoice' value='$invoice'>" .                      // 订单号
//            "<input type='hidden' name='charset' value='utf-8'>" .                              // 字符集
//            "<input type='hidden' name='no_shipping' value='1'>" .                              // 不要求客户提供收货地址
//            "<input type='hidden' name='no_note' value=''>" .                                  // 付款说明
//            "<input type='hidden' name='notify_url' value='$data_notify_url'>" .
//            "<input type='hidden' name='rm' value='2'>" .
//            "<input type='hidden' name='cancel_return' value='$cancel_return'>" .
//            "<input type='submit' value='" . $GLOBALS['_LANG']['paypal_button'] . "'>" .                      // 按钮
//            "</form><br />";
//        $url = '<br />'.
//        '<form style="text-align:center;" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_blank">'.
//            "<input type='hidden' name='cmd' value='_xclick'>".
//            "<input type='hidden' name='business' value='ceo@miraclequeen.com.au'>".
//            "<input type='hidden' name='item_name' value='2018012784491'>".
//            "<input type='hidden' name='amount' value='17.99'>".
//            "<input type='hidden' name='currency_code' value='USD'>".
//            "<input type='hidden' name='return' value='http://localhost/respond.php?code=paypal'>".
//            "<input type='hidden' name='invoice' value='68-localhost/respond'>".
//            "<input type='hidden' name='charset' value='utf-8'>".
//            "<input type='hidden' name='no_shipping' value='1'>".
//            "<input type='hidden' name='no_note' value=''>".
//            "<input type='hidden' name='notify_url' value='http://localhost/respond.php?code=paypal'>".
//            "<input type='hidden' name='rm' value='2'>".
//            "<input type='hidden' name='cancel_return' value='http://localhost/'>".
//            "<input type='submit' value='Use paypal immediately'>".
//            "</form><br />';
    }

}
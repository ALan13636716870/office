<?php
namespace Backend\Controller;

class OssController extends CommonController {

    private $_allowExt = array(
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png'
    );

    //获取参数
    function params(){
        $data = array(
            'expiration'=>gmdate('Y-m-d\TH:i:s\Z', time()+3600),
            'conditions'=>array(
                array('bucket'=>C('OSS_BUCKET'))
            )
        );
        $policy = base64_encode(json_encode($data));
        $signature = base64_encode(hash_hmac('sha1', $policy, C('OSS_ACCESS_SECRET'), true));

        $params = array(
            'OSSAccessKeyId' => C('OSS_ACCESS_ID'),
            'policy' => $policy,
            'Signature' => $signature,
            'keypre' => C('OSS_BUCKET_PRE') . date('Ym/d/'),
            'success_action_status' => '200'
        );

        $this->ajaxReturn($params);
    }

    //callback
    function callback(){
        header("Access-Control-Allow-Origin: " . C('OSS_HOST'));
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Authorization, Content-Type, Accept,Access-Control-Request-Headers,Access-Control-Allow-Methods,Access-Control-Allow-Origin,Access-Control-Allow-Credentials");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT, HEAD");
        header("Access-Control-Allow-Credentials: true");
        header("Content-type: application/json");
        $this->ajaxReturn(array('status'=>'1'));
    }

    //获取图片地址
    function photo(){
        $file = I('get.path');
        $width = I('get.width');
        $name = I('get.name');
        if(empty($file)){
            exit();
        }

        date_default_timezone_set('Europe/Lisbon');
        $expires = time()+60*60;
        $authStr = "GET\n\n\n".$expires."\n/".C('OSS_BUCKET')."/".$file;
        if(empty($name)){
            if($width>0)$authStr .= '@'.$width.'w';
            $disposition = 'response-content-disposition=inline';
        }else{
            $disposition = 'response-content-disposition=attachment; filename='.$name;
        }
        $authStr .= '?'.$disposition;
        $signature = base64_encode(hash_hmac('sha1', $authStr, C('OSS_ACCESS_SECRET'), true));
        $baseSign = str_replace(array('%2F', '%25'), array('/', '%'), rawurlencode($signature));
        $url = C('OSS_IMG_HOST').'/'.$file;
        if($width>0)$url .= '@'.$width.'w';
        $url .= '?OSSAccessKeyId='.C('OSS_ACCESS_ID').
            '&Expires='.$expires.'&Signature='.$baseSign.'&'.$disposition;
        header("Location: " . $url);
        exit();
    }

}

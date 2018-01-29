<?php

//获取某分类的直接子分类
function getSons($categorys,$catId=0){
    $sons=array();
    foreach($categorys as $item){
        if($item['parent_id']==$catId)
            $sons[]=$item;
    }
    return $sons;
}

//获取某个分类的所有子分类
function getSubs($categorys,$catId=0,$level=1){
    $subs=array();
    foreach($categorys as $item){
        if($item['parent_id']==$catId){
            $item['level']=$level;
            $subs[]=$item;
            $subs=array_merge($subs,getSubs($categorys,$item['id'],$level+1));
        }
    }
    return $subs;
}

//获取某个分类的所有父分类
//方法一，递归
function getParents($categorys,$catId){
    $tree=array();
    foreach($categorys as $item){
        if($item['id']==$catId){
            if($item['parent_id']>0)
                $tree=array_merge($tree,getParents($categorys,$item['parent_id']));
            $tree[]=$item;
            break;
        }
    }
    return $tree;
}

//方法二,迭代
function getParents2($categorys,$catId){
    $tree=array();
    while($catId != 0){
        foreach($categorys as $item){
            if($item['id']==$catId){
                $tree[]=$item;
                $catId=$item['parent_id'];
                break;
            }
        }
    }
    return $tree;
}


function getImageUrl($img, $width = 0, $noimg='/assets/image/nopic.gif'){
    if(empty($img)){
        return $noimg;
    }
    if(strpos($img, 'http://') === 0){
        return $img;
    }
    $url = U('oss/photo').'&path='.$img;
    if($width == 0){
        return $url;
    }
    return $url.'&width='.$width;
}



function array_sort($array, $on, $order=SORT_ASC){
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

function getRandChar($length = 4){
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol)-1;

    for($i=0;$i<$length;$i++){
        $str.=$strPol[mt_rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return strtoupper($str);
}

function genImageUrl($img){
    if(empty($img))return '';

    $company = session('company');
    if(!empty($company['oss_domain'])){
        return $company['oss_domain'].$img;
    }else{
        return C('OSS_PRODUCT_DOMAIN').$img;
    }
}


// 参数解释
// $string： 明文 或 密文
// $operation：DECODE表示解密,其它表示加密
// $key： 密匙
// $expiry：密文有效期
function authcode($string, $operation = 'DECODE', $key = 's&k_Gsk123', $expiry = 0) {
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 10;

    // 密匙
    $key = md5($key);

    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE') {
        // substr($result, 0, 10) == 0 验证数据有效性
        // substr($result, 0, 10) - time() > 0 验证数据有效性
        // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
        // 验证数据有效性，请看未加密明文的格式
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}


//删除目录
function rmdirr($dirname) {
    if (!file_exists($dirname)) {
        return false;
    }
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
    $dir = dir($dirname);
    if($dir){
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            //递归
            rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
        }
    }
    $dir->close();
    return rmdir($dirname);
}


function uni_decode($s) {   
    preg_match_all('/\&\#([0-9]{2,5})\;/', $s, $html_uni);   
    preg_match_all('/[\\\%]u([0-9a-f]{4})/ie', $s, $js_uni);   
    $source = array_merge($html_uni[0], $js_uni[0]);   
    $js = array();   
    for($i=0;$i<count($js_uni[1]);$i++) {   
        $js[] = hexdec($js_uni[1][$i]);   
    }   
    $utf8 = array_merge($html_uni[1], $js);   
    $code = $s;   
    for($j=0;$j<count($utf8);$j++) {   
        $code = str_replace($source[$j], unicode2utf8($utf8[$j]), $code);   
    }   
    return $code;//$s;//preg_replace('/\\\u([0-9a-f]{4})/ie', "chr(hexdec('\\1'))",  $s);   
}   
   
function unicode2utf8($c) {   
    $str="";   
    if ($c < 0x80) {   
         $str.=chr($c);   
    } else if ($c < 0x800) {   
         $str.=chr(0xc0 | $c>>6);   
         $str.=chr(0x80 | $c & 0x3f);   
    } else if ($c < 0x10000) {   
         $str.=chr(0xe0 | $c>>12);   
         $str.=chr(0x80 | $c>>6 & 0x3f);   
         $str.=chr(0x80 | $c & 0x3f);   
    } else if ($c < 0x200000) {   
         $str.=chr(0xf0 | $c>>18);   
         $str.=chr(0x80 | $c>>12 & 0x3f);   
         $str.=chr(0x80 | $c>>6 & 0x3f);   
         $str.=chr(0x80 | $c & 0x3f);   
    }   
    return $str;   
} 
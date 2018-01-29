<?php
/**
 * Created by PhpStorm.
 * User: gaosk
 * Date: 16/5/17
 * Time: 16:09
 */
namespace Backend\Controller;

use Think\Controller;

class ApiController extends Controller{

    //新版用chrome扩展只采集asin码的接口
    public function spider(){
        header('Access-Control-Allow-Credentials: true');
        // 指定允许其他域名访问
        header('Access-Control-Allow-Origin:*');
        // 响应类型
        header('Access-Control-Allow-Methods:POST');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $companyId = 1;
        $asinList = explode(',', $_GET['asin_list']);
        if($_GET['target'] == 'other'){
            $remUrl = 'http://spider.gaoshikao.cn/index.php?m=Amzdb&c=api&a=onlyasin&asins='.$_GET['asin_list'];
            $con = file_get_contents($remUrl);
            if($con == 'ok')exit("1");
            else exit('0');
        }
        if(empty($asinList)){
            exit("0");
        }

        $timenow = time();
        foreach($asinList as $asin){
            $one = array(
                'company_id' => $companyId,
                'asin' => $asin
            );
            $old = M('goods')->where(array('company_id'=>$companyId, 'asin'=>$asin))->count();
            if(!$old){
                $one['add_time'] = time();
                $one['last_rank'] = 0;
                $one['status'] = -1;
                M('goods')->add($one);
            }
            M('goods_history')->add(array('asin'=>$asin,'timestamp'=>$timenow));
        }
        M('goods_history')->where('timestamp<='.($timenow-24*3600))->delete();

        exit("1");
    }


    public function product($goodsIds = '',$start=0){
        $companyId = 1;
        $model = D('product');

        if(empty($goodsIds)){
            $_goodsList = $model->where("group_id=201706301")
                    ->order('last_update asc,id asc')->field('asin,id,country')->limit($start,5)->select();
            $goodsArr = array();
            foreach($_goodsList as $goods){
                $goodsArr[$goods['asin']] = $goods;
                $country = $goods['country'];
            }
            $goodsList = array_column($goodsArr, 'asin');
            $goodsIds = implode(',', $goodsList);
        }else{
            $_goodsInfo = $model->where('asin=\''.$goodsIds.'\'')->field('asin,id,country')->limit(1)->find();
            $goodsIds = $_goodsInfo['asin'];
            $goodsList = array($_goodsInfo['asin']);
            $country = $_goodsInfo['country'];
        }
        $data = $this->getasin($goodsList, $country);

        foreach($data as $one) {
            if(empty($one['ASIN']))continue;
            $goods = array(
                'asin' => $one['ASIN'],
                'parent_asin' => ($one['ParentASIN'] ? $one['ParentASIN'] : ''),
                'photo' => ($one['LargeImage'] ? $one['LargeImage'] : ''),
                'brand_name' => $one['ItemAttributes']['Brand'],
                'title' => $one['ItemAttributes']['Title'],
                'node_id' => ($one['BrowseNodeId'] ? $one['BrowseNodeId'] : '0'),
                'feature' => implode("\n",(array)$one['ItemAttributes']['Feature']),
                'publisher' => ($one['ItemAttributes']['Publisher'] ? $one['ItemAttributes']['Publisher'] : '')
            );

            $model->where(array('asin'=>$one['ASIN']))->save($goods);
        }

        foreach($goodsList as $ga){
            $model->where(array('asin'=>$ga))->save(array('last_update'=>time()));
        }

        exit($goodsIds."\n");
    }

    public function asin($goodsIds = '',$start=0){
        $companyId = 1;
        $model = D('goods');

        if(empty($goodsIds)){
            $_goodsList = $model->where('api_errors<3 and (status=-1)')
                ->order('last_rank asc,id asc')->field('asin,id')->limit($start,5)->select();
            $goodsArr = array();
            foreach($_goodsList as $goods){
                $goodsArr[$goods['asin']] = $goods;
            }
            $goodsList = array_column($goodsArr, 'asin');
            $goodsIds = implode(',', $goodsList);
        }else{
            $_goodsInfo = $model->where('asin=\''.$goodsIds.'\'')->field('asin,id')->limit(1)->find();
            $goodsIds = $_goodsInfo['asin'];
            $goodsArr = array($_goodsInfo['asin']=>$_goodsInfo);
            $goodsList = array($_goodsInfo['asin']);
        }
        $data = file_get_contents('http://restapi.sk.phpdict.com/asintoupc.php?asins='.$goodsIds);
        $data = json_decode($data, true);

        foreach($data as $one) {
            if(empty($one['ASIN']))continue;
            $goods = array(
                'last_rank'=>time(),
                'asin' => $one['ASIN'],
                'parent_asin' => ($one['ParentASIN'] ? $one['ParentASIN'] : ''),
                'photo' => ($one['LargeImage'] ? $one['LargeImage'] : ''),
                'brand' => $one['ItemAttributes']['Brand'],
                'title' => $one['ItemAttributes']['Title'],
                'node_id' => ($one['BrowseNodeId'] ? $one['BrowseNodeId'] : '0'),
                'feature' => implode("\n",(array)$one['ItemAttributes']['Feature']),
                'publisher' => ($one['ItemAttributes']['Publisher'] ? $one['ItemAttributes']['Publisher'] : '')
            );


            $model->where(array('company_id'=>$companyId,'asin'=>$one['ASIN']))->save($goods);

            foreach($goodsList as $gk => $ga){
                if($ga == $one['ASIN']){
                    unset($goodsList[$gk]);
                }
            }
        }

        foreach($goodsList as $ga){
            $model->where(array('company_id'=>$companyId,'asin'=>$ga))->setInc('api_errors');
        }

        exit($goodsIds."\n");
    }


    public function rank($goodsIds = '',$start=0){
        $companyId = 1;
        $model = D('goods');

        if(empty($goodsIds)){
            $_goodsList = $model->where('node_id=\'3081461011\'')
                ->order('last_update asc,id asc')->field('asin,id')->limit($start,5)->select();
            $goodsArr = array();
            foreach($_goodsList as $goods){
                $goodsArr[$goods['asin']] = $goods;
            }
            $goodsList = array_column($goodsArr, 'asin');
            $goodsIds = implode(',', $goodsList);
        }else{
            exit('not support');
        }
        $data = file_get_contents('http://restapi.sk.phpdict.com/asin.php?asin_list='.$goodsIds);
        $data = json_decode($data, true);

        foreach($data as $one) {
            $asin = $one['asin'];
            if(empty($asin))continue;
            $rank = intval($one['rank']['3081461011']);
            $goods = array(
                'last_update'=>time(),
                'ranks' => $rank
            );
            $model->where(array('company_id'=>$companyId,'asin'=>$asin))->save($goods);

            $goodsId = $goodsArr[$asin]['id'];
            M('goods_update')->add(array('goods_id'=>$goodsId), array(), true);

            foreach($goodsList as $gk => $ga){
                if($ga == $asin){
                    unset($goodsList[$gk]);
                }
            }
        }

        foreach($goodsList as $ga){
            $model->where(array('company_id'=>$companyId,'asin'=>$ga))->save(array('last_update'=>'4444444444'));
        }

        exit($goodsIds."\n");
    }

    public function subasin($asin){
        $companyId = 1;

        $url = 'http://www.amazon.com/dp/'.$asin.'/';
        $html = file_get_contents($url);
        preg_match_all('/data-defaultAsin="(.*?)"/is', $html, $matches);
        $result = array();
        foreach($matches[1] as $asin){
            $one = array(
                'company_id' => $companyId,
                'asin' => $asin
            );
            $old = M('goods')->where(array('company_id'=>$companyId, 'asin'=>$asin))->count();
            if(!$old){
                $one['add_time'] = time();
                $one['last_rank'] = -3;
                M('goods')->add($one);
            }
            $result[] = $asin;
        }

        $this->ajaxReturn(array('status'=>'1','asins'=>$result));
    }

    private function getasin($asinArr, $country = 'com'){
        define('AWS_API_KEY', 'AKIAJR5H4SWIZ6IJAMTA');
        define('AWS_API_SECRET_KEY', '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');

        if($country == 'co.jp'){
            define('AWS_ASSOCIATE_TAG', 'gaosk02-22');
        }else if($country == 'co.uk'){
            define('AWS_ASSOCIATE_TAG', 'gaoskuk07-21');
        }else{
            define('AWS_ASSOCIATE_TAG', 'gaosk0d-20');
        }

        $asin = implode(',',$asinArr);
        if(empty($asin)){
            return false;
        }

        $conf = new \ApaiIO\Configuration\GenericConfiguration();

        try {
            $conf
                ->setCountry($country)
                ->setAccessKey(AWS_API_KEY)
                ->setSecretKey(AWS_API_SECRET_KEY)
                ->setAssociateTag(AWS_ASSOCIATE_TAG);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        $apaiIO = new \ApaiIO\ApaiIO($conf);

        $lookup = new \ApaiIO\Operations\Lookup();
        $lookup->setItemId($asin);
        $lookup->setResponseGroup(array('Variations','SalesRank','ItemAttributes','Images','BrowseNodes'));

        $formattedResponse = $apaiIO->runOperation($lookup);
        $messageBody = \LSS\XML2Array::createArray($formattedResponse);

        $itemList = $messageBody['ItemLookupResponse']['Items']['Item'];
        if(!array_key_exists(0, $itemList)){
            $itemList = array($itemList);
        }
        foreach($itemList as $k => $item){
            $itemList[$k]['__FULL__'] = $item;
            unset($itemList[$k]['ItemLinks']);
            unset($itemList[$k]['SmallImage']);
            unset($itemList[$k]['MediumImage']);
            unset($itemList[$k]['ImageSets']);
            unset($itemList[$k]['ItemAttributes']['ItemDimensions']);
            unset($itemList[$k]['ItemAttributes']['PackageDimensions']);
            $itemList[$k]['LargeImage'] = $item['LargeImage']['URL'];
            if(isset($item['BrowseNodes']['BrowseNode'][0])){
                $itemList[$k]['BrowseNodeId'] = $item['BrowseNodes']['BrowseNode'][0]['BrowseNodeId'];
            }else{
                $itemList[$k]['BrowseNodeId'] = $item['BrowseNodes']['BrowseNode']['BrowseNodeId'];
            }

            unset($itemList[$k]['BrowseNodes']);
        }

        return $itemList;
    }




    //chrome扩展采集newrelease接口
    public function asins(){
        header('Access-Control-Allow-Credentials: true');
        // 指定允许其他域名访问
        header('Access-Control-Allow-Origin:*');
        // 响应类型
        header('Access-Control-Allow-Methods:POST');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $asinList = json_decode(uni_decode($_REQUEST['asin_list']), true);
        $titleList = json_decode(uni_decode($_REQUEST['title_list']), true);
        $photoList = json_decode(uni_decode($_REQUEST['photo_list']), true);
        $priceList = json_decode(uni_decode($_REQUEST['price_list']), true);
        $catid = trim($_REQUEST['catid']);
        $pg = intval($_REQUEST['pg']);
        $timenow = time();


        //新分类还是老分类
        if(empty($asinList)){
            M('product_hotnewreleases')->where(array('category_id'=>$catid))->save(array('last_spider'=>$timenow));
            $catid = 0;
            $pg = 1;
        }else{
            $pg++;
        }

        $catData = $this->asins_getcat($catid, $pg);
        $catid = $catData['cid'];

        //如果抓取了数据
        if(!empty($asinList)){
            foreach($asinList as $k => $asin){
                if(empty($asin))continue;
                $goods = $catData['data'];
                $goods['asin'] = $asin;
                $goods['photo'] = $photoList[$k];
                $goods['title'] = $titleList[$k];
                $goods['price'] = $priceList[$k];
                $old = M('product_newrelease')->where(array('asin'=>$asin))->count();
                if($old){
                    $goods['last_update'] = $timenow;
                     M('product_newrelease')->where(array('asin'=>$asin))->save($goods);
                }else{
                    $goods['add_time'] = $timenow;
                    $goods['last_update'] = $timenow;
                    M('product_newrelease')->add($goods);
                }
            }
        }

        exit(json_encode(array(
            'url' => $catData['url'],
            'pg' => $pg,
            'cat' => $catid
        )));
    }

    function asins_getcat($cid = 0, $pg = 1){


        //开始查找下一次要采集的链接地址
        $url = 'https://www.amazon.com/gp/new-releases/';

        //获取要采集的分类ID
        if(empty($cid)){
            $model = new \Think\Model();
            $sql = "SELECT h.category_id,h.category_name,h.parent_id,h.top_id,h.last_spider,hh.youxian FROM `product_hotnewreleases` h left join product_hotnewreleases hh on hh.category_id=h.top_id WHERE hh.youxian=1 order by h.last_spider asc,h.category_id asc limit 1";
            $catInfo = $model->query($sql)[0];
            $cid = $catInfo['category_id'];
        }else{
            $catInfo = M('product_hotnewreleases')->where(array('category_id'=>$cid))->find();
        }

        //获取分类树，以便存储商品数据
        $catArr = array($catInfo['category_id']);
        $currCatid = $catInfo['parent_id'];
        while($currCatid){
            $parentCatInfo = M('product_hotnewreleases')->where(array('category_id'=>$currCatid))->find();
            $catArr[] = $parentCatInfo['category_id'];
            $currCatid = $parentCatInfo['parent_id'];
        }
        $catArr = array_reverse($catArr);
        $catList = array('category_id','sub_category_id','third_category_id','fourth_category_id');

        $catData = array();
        foreach($catList as $k => $v){
            if(array_key_exists($k, $catArr)){
                $catData[$v] = $catArr[$k];
            }
        }

        //获取采集地址
        if(empty($catInfo['top_id'])){
            $url .= $catInfo['category_id'];
        }else{
            $url .= $catInfo['top_id'].'/'.$catInfo['category_id'];
        }
        $url .= '/?ie=UTF8&ajax=1&pg='.$pg;

        return array(
            'url' => $url,
            'data' => $catData,
            'cid' => $cid
        );
    }

    //采集popsocket，对搜索列表页通用
    public function popsocket(){
        header('Access-Control-Allow-Credentials: true');
        // 指定允许其他域名访问
        header('Access-Control-Allow-Origin:*');
        // 响应类型
        header('Access-Control-Allow-Methods:POST');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $asinList = json_decode(uni_decode($_POST['asin_list']), true);
        $titleList = json_decode(uni_decode($_POST['title_list']), true);
        $photoList = json_decode(uni_decode($_POST['photo_list']), true);
        $brandList = json_decode(uni_decode($_POST['brand_list']), true);
        $timestamp = uni_decode($_POST['timestamp']);

        foreach($asinList as $k => $asin){
            if(empty($asin))continue;
            $goods = array(
                'asin' => $asin,
                'photo' => $photoList[$k],
                'title' => $titleList[$k],
                'brand' => strip_tags($brandList[$k]),
                'add_time' => $timestamp
            );
            $old = M('popsocket')->where(array('asin'=>$asin))->count();
            if($old){
                 M('popsocket')->where(array('asin'=>$asin))->save($goods);
            }else{
                M('popsocket')->add($goods);
            }
        }

        exit("1");
    }

    //采集asin接口
    public function chrome_ext_asin(){
        header('Access-Control-Allow-Credentials: true');
        // 指定允许其他域名访问
        header('Access-Control-Allow-Origin:*');
        // 响应类型
        header('Access-Control-Allow-Methods:POST');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $hostIdArr = array(
            'com'=>1,
            'uk'=>3,
            'de'=>4,
            'fr'=>5,
            'jp'=>6,
            'ca'=>7,
            'cn'=>3240,
            'it'=>35691,
            'es'=>44551,
            'in'=>44571,
            'au'=>111172,
            'nl'=>328451,
            'br'=>526970,
            'mx'=>771770
        );

        $nodeId = uni_decode($_POST['node_id']);
        $nodeName = uni_decode($_POST['node_name']);
        $asin = uni_decode($_POST['asin']);
        $image = uni_decode($_POST['image']);
        $title = uni_decode($_POST['title']);
        $host = uni_decode($_POST['host']);
        $next = uni_decode($_POST['next']);
        $marketId = $hostIdArr[$host];

        if(empty($nodeId) || empty($nodeName) || empty($host) || empty($asin) || empty($image) || empty($title)){
            exit(json_encode(array('status'=>'0','msg'=>'数据不完整')));
        }

        $data = array(
            'asin' => $asin,
            'photo' => $image,
            'title' => $title,
            'node_id' => $nodeId,
            'node_name' => $nodeName,
            'market_id' => $marketId
        );
        $old = M('im_asin')->where(array('asin'=>$asin,'market_id'=>$marketId))->count();
        if($old){
             M('im_asin')->where(array('asin'=>$asin,'market_id'=>$marketId))->save($data);
        }else{
            M('im_asin')->add($data);
        }

        $nextUrl = '';
        if($next == 'true'){
            $nextAsin = M('im_asin')->where(array('title'=>''))->order('id desc')->limit(1)->find();
            if($nextAsin && $nextAsin['market_id'] == '1'){
                $nextUrl = 'https://www.amazon.com/dp/'.$nextAsin['asin'];
            }
        }
        exit(json_encode(array('status'=>'1','nexturl'=>$nextUrl)));
    }
}
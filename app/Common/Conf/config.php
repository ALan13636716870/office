<?php
return array(
    'MODULE_ALLOW_LIST' => array('Home','Backend'),
    'DEFAULT_MODULE'=>'Home',
    'DEFAULT_CONTROLLER'=>'Index',
    'URL_MODEL'=>0,
    'URL_CASE_INSENSITIVE' =>false,
    'SESSION_AUTO_START'=>true,
    'VAR_PAGE'=>'page',

    'TMPL_PARSE_STRING'  =>array(
        '__PUBLIC__' => '/assets',
        '__JS__'     => '/assets/js',
        '__CSS__'     => '/assets/css'
    ),

    //系统信息
    'OFFICE_NAME' => '尚酷办公系统',
    'OFFICE_VERSION' => 'V2.3.1',

    //OSS配置
    'OSS_ACCESS_ID' => 'Zdr7A3USNdbTbLM8',
    'OSS_ACCESS_SECRET'=>'8DYmKpzH9ULf6ug5g4H9yYteRtDUf5',
    'OSS_HOST'=>'http://customwatch.oss-us-west-1.aliyuncs.com',
    'OSS_IMG_HOST'=>'http://customwatch.oss-us-west-1.aliyuncs.com',
    'OSS_BUCKET'=>'customwatch',
    'OSS_ACL' => 1,

    //产品OSS
    'OSS_PRODUCT_BUCKET'=>'customwatch',
    'OSS_PRODUCT_DOMAIN'=>'http://product.img.phpdict.com/',
    'OSSCMD_PATH'=>'/data/htdocs/osscmd',
);
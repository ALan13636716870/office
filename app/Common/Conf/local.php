<?php

return array(
    //数据库配置
    'DB_TYPE'=>'mysql',
    'DB_HOST'=>'localhost',
    'DB_NAME'=>'amazon',
    'DB_USER'=>'phonecase',
    'DB_PWD'=>'a123456@',
    'DB_PORT'=>3306,
    'DB_PREFIX'=>'amazon_',
    'DB_CHARSET'=>'utf8',

    'OPENSEARCH_KEY' => 'Zdr7A3USNdbTbLM8',
    'OPENSEARCH_SECRET' => '8DYmKpzH9ULf6ug5g4H9yYteRtDUf5',
    'OPENSEARCH_API' => 'http://opensearch-cn-beijing.aliyuncs.com',

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
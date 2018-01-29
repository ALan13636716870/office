<?php

define('ROOT_PATH', dirname(__FILE__).'/');
define('APP_STATUS','local');
define('APP_DEBUG', APP_STATUS == 'local' ? true : false);
define('APP_PATH','./app/');

//生成图片配置
define('AMZP_PATH', ROOT_PATH.'data/');
define('AMZP_BUILD', 'build');        //生成的产品图根目录
define('AMZP_TEMP', 'temp');          //设计图的临时存放目录，用完删除
define('AMZP_MOULD', 'mould');        //模具图片的存放目录，不删除

require './ThinkPHP/ThinkPHP.php';

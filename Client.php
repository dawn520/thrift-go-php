<?php

namespace  batu\testDemo;
header("Content-type: text/html; charset=utf-8");
$startTime = getMillisecond();//记录开始时间

$ROOT_DIR = realpath(dirname(__FILE__).'/');
$GEN_DIR = realpath(dirname(__FILE__).'/').'/gen-php';
require_once $ROOT_DIR . '/Thrift/ClassLoader/ThriftClassLoader.php';

use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\TSocketPool;
use Thrift\Transport\TFramedTransport;
use Thrift\Transport\TBufferedTransport;

$loader = new ThriftClassLoader();
$loader->registerNamespace('Thrift',$ROOT_DIR);
$loader->registerDefinition('batu\demo', $GEN_DIR);
$loader->register();

$thriftHost = '127.0.0.1'; //UserServer接口服务器IP
$thriftPort = 9090;            //UserServer端口

$socket = new TSocket($thriftHost,$thriftPort);
$socket->setSendTimeout(10000);#Sets the send timeout.
$socket->setRecvTimeout(20000);#Sets the receive timeout.
//$transport = new TBufferedTransport($socket); #传输方式：这个要和服务器使用的一致 [go提供后端服务,迭代10000次2.6 ~ 3s完成]
$transport = new TFramedTransport($socket); #传输方式：这个要和服务器使用的一致[go提供后端服务,迭代10000次1.9 ~ 2.1s完成，比TBuffer快了点]
$protocol = new TBinaryProtocol($transport);  #传输格式：二进制格式
$client = new \batu\demo\batuThriftClient($protocol);# 构造客户端

$transport->open();
$socket->setDebug(TRUE);

for($i=1;$i<11;$i++){
    $item = array();
    $item["a"] = "batu.demo";
    $item["b"] = "test".$i;
    $result = $client->CallBack(time(),"php client",$item); # 对服务器发起rpc调用
    echo "PHPClient Call->".implode('',$result)."\n";
}

$s = new \batu\demo\Article();
$s->id = 1;
$s->title = '插入一篇测试文章';
$s->content = '我就是这篇文章内容';
$s->author = 'liuxinming';
$client->put($s);

$s->id = 2;
$s->title = '插入二篇测试文章';
$s->content = '我就是这篇文章内容';
$s->author = 'liuxinming';
$client->put($s);

$endTime = getMillisecond();

echo "本次调用用时: :".$endTime."-".$startTime."=".($endTime-$startTime)."毫秒<br>";

function getMillisecond() {
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}

$transport->close();
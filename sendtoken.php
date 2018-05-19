<?php
error_reporting(E_ALL);
require_once('eth.rpc.php');
$_EnryEWT_ETH = new EnryEWT_ETH();

$from = '0x51da3ed5edd1bca0d1c49055e25afe562e4edb91';//平台充提币唯一地址
$to = '0x317ab3dfc78ec41835394e26521a00e45430d870';//散户钱包地址
$pwd = '123456';//账号密码
$contract = '0xa9902cc787678f5d833b4cab0f6067d45e75cc1f';//合约地址
$amount = 500000;//十进制
$value_tpl = '0000000000000000000000000000000000000000000000000000000000000000';//64位固定长度
$value_tpl_cnt = strlen($value_tpl);
$dechex = str_replace('0x','',$_EnryEWT_ETH->toHexWei($amount));//十六进制
$dechex_cnt = strlen($dechex);
$value = substr($value_tpl,0,64-$dechex_cnt).$dechex;

//echo ('0xcdcd77c000000000000000000000000000000000000000000000000000000000000000450000000000000000000000000000000000000000000000000000000000000001');
//$data = '0xa9059cbb'.'000000000000000000000000'.str_replace('0x','',$to).$value;//固定长度138位
$data = '0xa9059cbb000000000000000000000000317ab3dfc78ec41835394e26521a00e45430d8700000000000000000000000000000000000000000000069e10de76676d0800000';
$_EnryEWT_ETH->unlockAccounts($from, $pwd);

$data = $_EnryEWT_ETH->sendTransaction($from,$contract,0,$data);
var_dump($data);

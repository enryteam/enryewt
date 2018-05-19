<?php
error_reporting(E_ALL);
////以太坊钱包//////////////////////////START////////////////////
require_once('eth.rpc.php');
$_EnryEWT_ETH = new EnryEWT_ETH();

////区块查询
$contract = '0x86fa049857e0209aa7d9e616f7eb3b3b78ecfdb0';//合约地址
$address = '0x73fce21ee624b74c08af6141578cfed9ec056287';//充币地址
$eventlogdb = 'eventlogdb.php';//交易区块
if(!file_exists($eventlogdb))
{
  file_put_contents($eventlogdb,'<?php exit;?>');
}
while(1)
{

  $ret = json_decode($_EnryEWT_ETH->_newFilter($contract,0,'latest'),TRUE);
  //var_dump($ret['result']);
  $log = json_decode($_EnryEWT_ETH->filterchanges($ret['result']),TRUE);//eth_getFilterLogs
  if(strlen($log['result'][0]['transactionHash'])==66)
  {
    $info = json_decode($_EnryEWT_ETH->getTransactionByHash($log['result'][0]['transactionHash']),TRUE);
    //var_dump($info);
    $hash = $info['result']['hash'];//唯一
    $from = $info['result']['from'];//发
    $to = '0x'.substr($info['result']['input'],34,40);//收
    $amout = $_EnryEWT_ETH->toEth('0x'.substr($info['result']['input'],-64));//16转10进制
    var_dump(array('hash'=>$hash,'from'=>$from,'to'=>$to,'amout'=>$amout));

    if($address==$to&&strlen($address)==42&&strlen($to)==42)
    {

      $eventlog = str_replace('<?php exit;?>','',file_get_contents('eventlogdb.php'));
      $logs = array();
      if(strpos($eventlog,'hash'))
      {

        if(!strpos($eventlog,$hash)&&!file_exists('eventlogdb.lock'))
        {
          file_put_contents('eventlogdb.lock',time());
          $logs = unserialize($eventlog);
          $logs[] = array('hash'=>$hash,'from'=>$from,'to'=>$to,'amout'=>$amout);
          file_put_contents($eventlogdb,'<?php exit;?>'.serialize($logs));
          unlink('eventlogdb.lock');
        }


      }
      else
      {
        $logs[] = array('hash'=>$hash,'from'=>$from,'to'=>$to,'amout'=>$amout);
        echo '<?php exit;?>'.serialize($logs);
        file_put_contents($eventlogdb,'<?php exit;?>'.serialize($logs));
      }
    }

  }
//  var_dump($log);
  //file_put_contents('event.log',$log,FILE_APPEND);
  sleep(1);
}



////以太坊钱包//////////////////////////OVER////////////////////

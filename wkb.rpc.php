<?php
// $WKB = new EnryEWT_WKB();
// var_dump($WKB->GetTradesByAddress('0x30d6318dda5a2e4d0eaed4b2cd0976b42a62d675'));
class EnryEWT_WKB {
  //玩客币官方接口地址
  private static $restapi = "https://walletapi.onethingpcs.com";

  //根据玩客币地址获取交易
  public function GetTradesByAddress($address)
  {
    $post_data = '["'.$address.'",  "0", "0", "1", "99"]';

    return self::doCurl(self::$restapi."/getTransactionRecords",$post_data);
  }


  //根据玩客币地址获取余额
  public function GetBalanceByAddress($address)
  {
    //$address = '0x30d6318dda5a2e4d0eaed4b2cd0976b42a62d675';
    //结果比对 http://blockchain.alphamao.top/index.php?address=0x30d6318dda5a2e4d0eaed4b2cd0976b42a62d675
    $post_data = '{"jsonrpc":"2.0","method":"eth_getBalance","params":["'.$address.'", "latest"],"id": 1}';
    $ret = self::doCurl((self::$restapi)."/getBalance",$post_data);

    return !$ret?false:hexdec($ret['result'])/1e18;
  }


  private static function doCurl($url,$post_data)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') {
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        $resArr = json_decode($body,true);
        return $resArr;
    }
    else {
      return false;
    }
    curl_close($ch);
  }
}

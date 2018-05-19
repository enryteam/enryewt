<?php
class EnryEWT_ETH {
  // for product
  private static $address = "http://1.2.3.4";
  private static $port = 8888;

  // for private test
  // private static $address = "http://localhost";
  // private static $port = 8545;

  // Warning! don't modify this parmeretes.
  private static $v = "2.0";
  private static $h = 1e18;
  private static $hs = "1000000000000000000";

  // get net version
  public static function getNetVersion()
  {
      $data = self::generateRequestData("net_version");
      return self::request(json_encode($data));
  }

  public static function blockNumber()
  {
      $data = self::generateRequestData("eth_blockNumber");
      return self::request(json_encode($data));
  }

  public static function newAccount($pwd = 'yh02sz55y8h8szy629h2s0z')
  {
      $params = [$pwd];
      $data = self::generateRequestData("personal_newAccount", $params);
      return self::request(json_encode($data));
  }

  //eth的账号余额
  public static function getBalance($addr, $tag = "latest")
  {
      self::validateEthAddress($addr);
      $params = [$addr, $tag];
      $data = self::generateRequestData("eth_getBalance", $params);
      return self::request(json_encode($data));
  }

	//查询所有地址
  public static function accounts()
  {
      $data = self::generateRequestData("eth_accounts");
      return self::request(json_encode($data));
  }


  public static function unlockAccounts($addr, $pwd, $duration = 60)
  {
      $params = [$addr, $pwd, $duration];
      $data = self::generateRequestData("personal_unlockAccount", $params);
      return self::request(json_encode($data));
  }

	//查询子地址时给出的是1
  public static function lockAccounts($addr)
  {
      $params = [$addr];
      $data = self::generateRequestData("personal_lockAccount", $params);
      return self::request(json_encode($data));
  }
  public static function filterchanges($filterid)
  {
    $params = [$filterid];
    $data = self::generateRequestData("eth_getFilterLogs", $params);
    return self::request(json_encode($data));
  }

  public static function newFilter($address)
  {
      return self::_newFilter($address);
  }


  public static function _newFilter($address, $fromBlock = 0, $toBlock = 0, $topics = [])
  {
      $params = [
          "address" => $address,
      ];
      if ($fromBlock > 0) {
          $params['fromBlock'] = $fromBlock;
      }
      if ($toBlock > 0) {
          $params['toBlock'] = $toBlock;
      }
      if (!empty($topics)) {
          $params['topics'] = $topics;
      }
      $data = self::generateRequestData("eth_newFilter", [(object) $params]);
      //var_dump(json_encode($data));
      return self::request(json_encode($data));
  }

  // transfer
  public static function transaction($from, $to, $value, $pwd){
      self::validateEthAddress($from);
      self::validateEthAddress($to);

      $unlockInfo = json_decode(self::unlockAccounts($from, $pwd));
      if (!isset($unlockInfo->result) || !$unlockInfo->result ){
          return $unlockInfo;
      }

      $transferInfo = json_decode(self::sendTransaction($from, $to, $value));
      self::lockAccounts($from);

      return $transferInfo;
  }

  // manual transfer, unlock first, then sendTransfer, and lock account last;
  public static function sendTransaction($from, $to, $value)
  {
      return self::_sendTransaction($from, $to, $value);
  }

  // send transaction full pars
  public static function _sendTransaction($from, $to, $value, $gas = 0, $gasPrice = 0, $data = "", $nonce = 0)
  {
      $params = [
          "from" => $from,
          "to" => $to,
          "value" => self::toHexWei($value),
      ];
      if ($gas > 0) {
          $params['gas'] = $gas;
      }
      if ($gasPrice > 0) {
          $params['gasPrice'] = $gasPrice;
      }
      if (strlen($data) > 0) {
          $params['data'] = $data;
      }
      if ($nonce > 0) {
          $params['nonce'] = $nonce;
      }
      $data = self::generateRequestData("eth_sendTransaction", [(object) $params]);
      //echo "<pre>";
      //print_r(json_encode($data));
      return self::request(json_encode($data));
  }

  public static function getTransactionByHash($hash)
  {
      self::validateEthAddress($hash);
      $params = [$hash];
      $data = self::generateRequestData("eth_getTransactionByHash", $params);
      return self::request(json_encode($data));
  }

  public static function toDec($hexAmount)
  {
      return hexdec($hexAmount);
  }

	//hash查询结果中的value值  转换为10进制
  public static function toEth($hexAmount)
  {
      return self::toDec($hexAmount) / self::$h;
  }

  public static function toWei($eth)
  {
      return $eth * self::$h;
  }

  public static function toHex($eth)
  {
      return "0x" . dechex($eth);
  }

  public static function toHexWei($eth)
  {
      //return "0x" . dechex(self::toWei($eth));
      return "0x" . base_convert(self::calc($eth, self::$hs, "mul"), 10, 16);
  }

  private static function validateEthAddress($addr)
  {
      if (strlen($addr) < 10 || substr($addr, 0, 2) != "0x") {
          echo "Invalid address:". $addr;
          exit();
      }
  }

  private static function generateRequestData($method, $params = [])
  {
      $data = [
          "jsonrpc" => self::$v,
          "method" => $method,
          "params" => $params,
          "id" => mt_rand(1, 100000000),
      ];
      return $data;
  }

  private static function request($post_data)
  {
      if (strlen(self::$address) <= 0 || self::$port <= 0) {
          echo "eth client address or port error";
          exit();
      }
      $url = self::$address . ":" . self::$port;
      return self::post($url, $post_data);
  }

  // for big number
  private static function calc($m, $n, $x)
  {
      $errors = array(
          '被除数不能为零',
          '负数没有平方根'
      );
      switch ($x) {
          case 'add':
              $t = bcadd($m, $n);
              break;
          case 'sub':
              $t = bcsub($m, $n);
              break;
          case 'mul':
              $t = bcmul($m, $n);
              break;
          case 'div':
              if ($n != 0) {
                  $t = bcdiv($m, $n);
              } else {
                  return $errors[0];
              }
              break;
          case 'pow':
              $t = bcpow($m, $n);
              break;
          case 'mod':
              if ($n != 0) {
                  $t = bcmod($m, $n);
              } else {
                  return $errors[0];
              }
              break;
          case 'sqrt':
              if ($m >= 0) {
                  $t = bcsqrt($m);
              } else {
                  return $errors[1];
              }
              break;
      }
      $t = preg_replace("/\..*0+$/", '', $t);
      return $t;
  }

  // curl for request
  private static function post($url, $post_data = '', $timeout = 10)
  {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, 1);
      if ($post_data != '') {
          curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
      }
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
      curl_setopt($ch, CURLOPT_HEADER, false);
      $file_contents = curl_exec($ch);
      curl_close($ch);
      return $file_contents;
  }
}

?>

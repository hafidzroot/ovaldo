<?php

date_default_timezone_set('Asia/Jakarta');

error_reporting(0);
if (!file_exists('token')) {
    mkdir('token', 0777, true);
}
function gojek($url, $token = null, $data = null, $pin = null)
{
$header[] = "Host: api.gojekapi.com";
$header[] = "User-Agent: okhttp/3.10.0";
$header[] = "Accept: application/json";
$header[] = "Accept-Language: en-ID";
$header[] = "Content-Type: application/json; charset=UTF-8";
$header[] = "X-AppVersion: 3.16.1";
$header[] = "X-UniqueId: 942a440b6222afd6";
$header[] = "Connection: keep-alive";    
$header[] = "X-User-Locale: en_ID";
$header[] = "X-Location: -6.2845103,107.0194598";
$header[] = "X-Location-Accuracy: 3.0";
if($token):
$header[] = "Authorization: Bearer $token";
endif;
if($pin):
    $header[] = "pin: $pin";
endif;
// print_r($header);
$c = curl_init("https://api.gojekapi.com".$url);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    if ($data):
    curl_setopt($c, CURLOPT_POSTFIELDS, $data);
    curl_setopt($c, CURLOPT_POST, true);
    endif;
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_HEADER, true);
    curl_setopt($c, CURLOPT_HTTPHEADER, $header);
    // if ($socks):
          // curl_setopt($c, CURLOPT_HTTPPROXYTUNNEL, true); 
          // curl_setopt($c, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5); 
          // curl_setopt($c, CURLOPT_PROXY, $socks);
        // endif; 
    $response = curl_exec($c);
    $httpcode = curl_getinfo($c);
    if (!$httpcode)
        return false;
    else {
        $header = substr($response, 0, curl_getinfo($c, CURLINFO_HEADER_SIZE));
        $body   = substr($response, curl_getinfo($c, CURLINFO_HEADER_SIZE));
    }
    $json = json_decode($body, true);
    return $json;

}

    function login($no)
    {
    $data = '{"phone":"+'.$no.'"}';
    $login = gojek("/v4/customers/login_with_phone", "", $data);
    // print_r($login);
    if ($login['success'] == 1)
        {
        return $login;
        }
      else if($login['errors'])
        {
        return $login;
        }
        else
        {
        return array("errors" => array("message" => "Unknown Error"));
        }
    }
function veriflogin($otp, $token)
    {
    $data = '{"client_name":"gojek:cons:android","client_secret":"83415d06-ec4e-11e6-a41b-6c40088ab51e","data":{"otp":"'.$otp.'","otp_token":"'.$token.'"},"grant_type":"otp","scopes":"gojek:customer:transaction gojek:customer:readonly"}';
    $verif = gojek("/v4/customers/login/verify", "", $data);
    if ($verif['success'] == 1)
        {
        return $verif;
        }
      else if($verif['errors'])
      {
        return $verif;
      }
      else 
      {
            return array("errors" => array("message" => "Unknown Error"));
      }
    }
    function loginB()
    {
        login:
        echo "[+] Nomor ? : ";
        $number = trim(fgets(STDIN));
        $login = login($number);
        if($login['success'] == 1)
        {
            OTP:
            echo "[+] Enter OTP ? : ";
            $otp = trim(fgets(STDIN));
            $verify = veriflogin($otp, $login['data']['login_token']);
            if($verify['success'] == 1){
                    file_put_contents("token/".$verify['data']['customer']['name'].".txt", $verify['data']['access_token']);
            }
            else
                {
                echo $verify['errors'][0]['message']."  RE ENTER ? (Y/N)\n";
                if(strtoupper(trim(fgets(STDIN))) == "Y")
                {
                goto OTP;
                }
                else
                {
                    goto login;
                }
            }
        }
        else
        {
            echo $login['errors'][0]['message']."\n";
            goto login;
        }
    }
    function send()
    {
        $dir = scandir('token');
        unset($dir[0]);
        unset($dir[1]);
        foreach($dir as $dirs)
        {
            $tok[] = str_replace('.txt', '', $dirs);
        }
        if(count($tok) == 1)
        {
            $choose = "0";
        }
        else
        {
            $end = count($tok) - 1;
            $choose = "0 - $end";
        }
        choose:
        // echo "[+] Choose Account ($choose): ";
        $choosed = "0";
        if(empty(file_get_contents("token/".$tok[$choosed].".txt")))
        {
            echo "Token Not Found\n\n";
            goto choose;
        }
        $token = file_get_contents("token/".$tok[$choosed].".txt");
        echo "     ======================================\n";
        echo "    | Thanks To : ".$tok[$choosed]." |\n";
        echo "    | Author    : Krisna Dwi Mahendra      |\n";
        echo "    | Recode    : Abdul Hafiz    |\n";
        echo "    | Date      : ".date('d-m-Y H:i:s')."      |\n";
        echo "     ========== GOPAY SENDER V.1 ==========\n";

            $profile = gojek("/wallet/profile" ,$token);
            $balance = $profile['data']['balance'];
            echo "\n[+] Sisa Saldo : ".(number_format($balance));
            nomor:
            echo "\n[+] Nomor ? : ";
            $number = trim(fgets(STDIN));
            $qr = gojek("/wallet/qr-code?phone_number=%2B$number", "$token");
            if(!isset($qr['data']))
            {
                if($qr['errors'])
                {
                    echo "[+] ".$qr['errors'][0]['message']."\n";
                }
                else {
                    echo "Unknown Error\n";
                }
                goto nomor;
            }
            else
            {
                $qrid = $qr['data']['qr_id'];
                $name = $qr['data']['name'];
                // echo "Input Transfer Amount: ";
                $amount = "2";
                echo "[+] Processing Transfer Rp.".number_format($amount)." To ".$number."\n";
                pin:
                // echo "Please Enter Your PIN: ";
                $pin = "180118";
                if(!is_numeric($pin))
                {
                    echo "Wrong Format PIN\n\n";
                    goto pin;
                }
                if(strlen($pin) !== 6)
                {
                    echo "PIN IS 6 DIGIT\n\n";
                    goto pin;
                }
                $tf = transfer($qrid, $token, $amount, $pin);
            if(!isset($tf['data']))
            {
                if($tf['errors'])
                {
                    echo "[+] ".$tf['errors'][0]['message']."\n";
                }
                else {
                    echo "Unknown Error\n";
                }
            }
            else
            {
                echo "[+] Success Transaction => ".$tf['data']['transaction_ref']."\n";
                echo "[+] Ulangi y/n ? : ";
                $ulangi = trim(fgets(STDIN));
                if ($ulangi == 'y') {
                    goto nomor;
                }
            

                        }
            }
        }
    function transfer($qrid, $token, $amount, $pin)
    {
        $data = '{"amount":"'.$amount.'","description":null,"qr_id":"'.$qrid.'"}';
        return gojek("/v2/fund/transfer", $token, $data, $pin);
    }
    function is_dir_empty($dir) 
    {
    if (!is_readable($dir)) return NULL; 
    return (count(scandir($dir)) == 2);
    }
    if(is_dir_empty('token'))
    {
        echo "No Token Found Please Login \n";
        loginB();
    }
    else
    {
        // echo "Add Account ? (y/n): ";
        $ans = "n";
        if(strtolower($ans) == 'y')
        {
            loginB();
        }
        send();   

         }


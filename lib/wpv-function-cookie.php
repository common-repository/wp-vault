<?php
class WpvLinkManagerCookie {
    function GetCookieData() {
        if (isset($_COOKIE[md5("wp-vault-link-manager-" . COOKIEHASH)])) {
            return split(",", base64_decode($_COOKIE[md5("wp-vault-link-manager-" . COOKIEHASH)]));
        }
        else {
            return array("-1", "post");
        }
    }
    
    function SetCookieData() {
        if (isset($_POST["proc"]) && isset($_POST["post_id"])) {
            setcookie(md5("wp-vault-link-manager-" . COOKIEHASH), base64_encode($_POST["post_id"]. "," . $_POST["proc"]), time()+3600, COOKIEPATH, COOKIE_DOMAIN);
        }
    }
    
    function DeleteCookieData() {
        setcookie(md5("wp-vault-link-manager-" . COOKIEHASH), "", time()-3600, COOKIEPATH, COOKIE_DOMAIN);
    }
}

class WpvFTPGetCookie {
    function GetCookieData() {
        if (isset($_COOKIE[md5("wp-vault-ftp-get-" . COOKIEHASH)])) {
            $cookie_array = split("///", WpvCookieEncryption::DecryptCookie($_COOKIE[md5("wp-vault-ftp-get-" . COOKIEHASH)]));
            $cookie_array[1] = base64_decode($cookie_array[1]);
            
            return $cookie_array;
        }
        else {
            return FALSE;
        }
    }
    
    function SetCookieData() {
        if (isset($_POST["host"]) && isset($_POST["user_id"]) && isset($_POST["password"]) && isset($_POST["port"])) {
            $cookie_data = WpvCookieEncryption::EncryptCookie($_POST["user_id"] . "///" . base64_encode($_POST["password"]) . "///" . $_POST["host"] . "///". $_POST["port"]);
            
            setcookie(md5("wp-vault-ftp-get-" . COOKIEHASH), $cookie_data, time()+600, COOKIEPATH, COOKIE_DOMAIN);
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
    
    function ResetCookieData() {
        if (isset($_COOKIE[md5("wp-vault-ftp-get-" . COOKIEHASH)])) {
            $cookie_data = $_COOKIE[md5("wp-vault-ftp-get-" . COOKIEHASH)];
            
            setcookie(md5("wp-vault-ftp-get-" . COOKIEHASH), $cookie_data, time()+600, COOKIEPATH, COOKIE_DOMAIN);
            return TRUE;
        }
        else {
            return FALSE;
        }
    }

    function DeleteCookieData() {
        if (isset($_COOKIE[md5("wp-vault-ftp-get-" . COOKIEHASH)])) {
            $cookie_data = $_COOKIE[md5("wp-vault-ftp-get-" . COOKIEHASH)];
            
            setcookie(md5("wp-vault-ftp-get-" . COOKIEHASH), $cookie_data, time()-3000, COOKIEPATH, COOKIE_DOMAIN);
        }
    }
}

class WpvCookieEncryption {
    function EncryptCookie($value){
       if(!$value){return FALSE;}
       
       $key = substr(COOKIEHASH, 0, 10);
       $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
       $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
       $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $value, MCRYPT_MODE_ECB, $iv);
       return trim(base64_encode($crypttext));
    }

    function DecryptCookie($value){
       if(!$value){return FALSE;}
       
       $key = substr(COOKIEHASH, 0, 10);
       $crypttext = base64_decode($value);
       $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
       $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
       $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
       return trim($decrypttext);
    }
}
?>
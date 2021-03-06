<?php
/**
 * Helper 
 * 
 * @package 
 * @version 0.0.1
 * @copyright 2014-2015 Liryan
 * @author Ryan <canbetter@qq.com> 
 * @license MIT
 */
namespace WechatBot\Helper;
class Helper
{
    /**
     * post 
     * 
     * @param mixed $data 
     * @param mixed $url 
     * @param string $cert 只支持pem格式的
     * @param string $proxy 
     * @static
     * @access public
     * @return void
     */
    public static function getCookiePath($url)
    {
        $root="/tmp/";
        $info=parse_url($url);
        $host=$info['host'];
        return $root.sha1($host).".cookie";
    }

    public static function  post($data,$url,$cert='',$proxy='')
    {
        $ch = curl_init();
        static::msg("Post:$url"." data:".$data);
        curl_setopt($ch, CURLOPT_TIMEOUT,5);
        curl_setopt($ch, CURLOPT_COOKIEFILE,static::getCookiePath($url));
        curl_setopt($ch, CURLOPT_COOKIEJAR,static::getCookiePath($url));
        if($proxy){ 
            curl_setopt($ch,CURLOPT_PROXY, $proxy['proxy_ip']);
            curl_setopt($ch,CURLOPT_PROXYPORT, $proxy['proxy_port']);
        }
        curl_setopt($ch,CURLOPT_URL, $url);
        $urlinfo=parse_url($url);
        if(strcasecmp($urlinfo['scheme'],"https")==0){
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);//2:严格校验
        }
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
        if($cert){
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $cert['cert']);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $cert['key']);
        }
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        } else { 
            $error = curl_errno($ch);
            curl_close($ch);
            throw new \Exception("Curl get error,CODE:$error");
        }
    }

    public static function get($url,$cert='')
    {
        $ch = curl_init();  
        static::msg("get:$url");
        curl_setopt($ch,CURLOPT_URL,$url);
        $urlinfo=parse_url($url);
        curl_setopt($ch, CURLOPT_COOKIEFILE,static::getCookiePath($url));
        curl_setopt($ch, CURLOPT_COOKIEJAR,static::getCookiePath($url));
        if(strcasecmp($urlinfo['scheme'],"https")==0){
            curl_setopt($ch, CURLOPT_PORT, 443);  
            //curl_setopt($ch, CURLOPT_SSLVERSION, 3);  
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);  //信任任何证书 
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);      //2:严格校验
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($cert){
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $cert['cert']);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $cert['key']);
        }
        $data = curl_exec($ch);  
        echo $data;
        if($data){
            curl_close($ch);
            return $data;
        }else{ 
            $error = curl_errno($ch);
            curl_close($ch);
            throw new \Exception("Curl get error,CODE:$error");
        }
    }

    public static function getMillisecond()
    {
        $tm=gettimeofday();
        return $tm['sec']*1000+round($tm['usec']/1000);
    }

    public function getDeviceId()
    {
        return "e".mt_rand(10000000,99999999).mt_rand(1000000,9999999);
    }

    public static function msg($msg)
    {
        echo Date('Y-m-d H:i:s')."\t".$msg."\n";
    }

    public static function outImg($url)
    {
        echo '<div style="text-align:center"><h1>微信扫码登录机器人</h1><img src="'.$url.'"></div>';
    }
}

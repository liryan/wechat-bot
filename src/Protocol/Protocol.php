<?php
namespace WechatBot\Protocol;
use WechatBot\Helper\Helper;
class Protocol
{
    public $uuid_url="https://login.wx.qq.com/jslogin?";
    public $qrcode_url="https://login.weixin.qq.com/qrcode/";
    public function requestUuid()
    {
        $tm=gettimeofday();
        $data=[
            'appid'=>'wx782c26e4c19acffb',
            'redirect_uri'=>'https://web.weixin.qq.com/cgi-bin/mmwebwx-bin/webwxnewloginpage',
            'fun'=>'new',
            'lang'=>'zh_CN',
            '_'=>$tm['sec']*1000+round($tm['usec']/1000),
        ];
        $data=Helper::get($this->url.http_build_query($data));
        if(preg_match("/QRLogin\.code[ ]*=[ ]*([0-9]+)[^\"]+\"([^\"]+)\"/i",$data,$match)){
            $code=$match[1];
            $uuid=$match[2];
        }
        return $uuid;
    }

    public function requestQrcode($uuid)
    {
        return $this->qrcode_url.$uuid;
    }
}

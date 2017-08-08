<?php
namespace WechatBot\Protocol;
use WechatBot\Helper\Helper;
class Protocol
{
    const CODE_ERROR                =0;
    const CODE_SCANED               =201;
    const CODE_LOGINED              =200;
    const CODE_TIMEOUT              =408;
    const CODE_SYNC_NORM            =0;
    const CODE_SYNC_EXIT            =1100;
    const CODE_SYNC_STATUS_NORM     =0;
    const CODE_SYNC_STATUS_NEW      =2;
    const CODE_SYNC_STATUS_ENTRY    =7;

    public $uuid_url                ="https://login.wx.qq.com/jslogin?";
    public $qrcode_url              ="https://login.weixin.qq.com/qrcode/";
    public $login_check_url         ="https://login.wx.qq.com/cgi-bin/mmwebwx-bin/login?";
    public $init_url                ="https://wx2.qq.com/cgi-bin/mmwebwx-bin/webwxinit?";
    public $sync_check_url          ="https://webpush.wx2.qq.com/cgi-bin/mmwebwx-bin/synccheck?";
    public $notify_url              ="https://wx2.qq.com/cgi-bin/mmwebwx-bin/webwxstatusnotify?";
    public $msg_rsync_url           ="https://wx2.qq.com/cgi-bin/mmwebwx-bin/webwxsync?";

    public function requestUuid()
    {
        $data=[
            'appid'=>'wx782c26e4c19acffb',
            'redirect_uri'=>'https://web.weixin.qq.com/cgi-bin/mmwebwx-bin/webwxnewloginpage',
            'fun'=>'new',
            'lang'=>'zh_CN',
            '_'=>Helper::getMillisecond(),
        ];
        $data=Helper::get($this->url.http_build_query($data));
        if(preg_match("/QRLogin\.code[ ]*=[ ]*([0-9]+)[^\"]+\"([^\"]+)\"/i",$data,$match)){
            $code=$match[1];
            $uuid=$match[2];
        }
        return $uuid;
    }

    public function getQrcodeUrl($uuid)
    {
        return $this->qrcode_url."$uuid";
    }

    public function getLoginCode($uuid)
    {
        $data=[
            'loginicon'=>'true',
            'uuid'=>$uuid,
            'tip'=>0,
            'r'=>1066062654,
            '_'=>Helper::getMillisecond()
        ];
        $data=Helper::get($this->login_check_url.http_build_query($data));
        $code=self::CODE_ERROR;
        $url='';
        if(preg_match("/window.code[ }*=[ ]*([0-9]+);/i",$data,$match)){
            $code=$match[1];
        }
        if($code==self::CODE_LOGINED){
            if(preg_match("/window.redirect_uri=\"([^\"]+)\"/i",$data,$match)){
                $url=$match[1];
            }
        }
        return ['code'=>$code,'url'=>$url];
    }

    public function getCookie($url)
    {
        $xml=Helper::get($url."&fun=new&version=v2&lang=zh_CN");
        $obj=simplexml_load_string($xml);
        $deviceid="e".mt_rand(10000000,99999999).mt_rand(1000000,9999999);
        return [
            'cookie'=>[
                'BaseRequest'=>[
                    'Uin'=>$obj->wxuin,
                    'Sid'=>$obj->wxsid,
                    'Skey'=>$obj->skey,
                    'DeviceID'=>$deviceid,
                ]
            ],
            'pass_ticket'=>$obj->pass_ticket,
            'uin'=>$obj->wxuin,
            'sid'=>$obj->wxsid,
            'skey'=>$obj->skey,
            'deviceid'=>$deviceid,
        ];
    }

    public function init($cookie,$ticket)
    {
        $params=[
            'r'=>'',
            'lang'=>'zh_CN',
            'pass_ticket'=>$ticket,
            '_'=>Helper::getMillisecond()
        ];
        $data=json_encode($cookie);
        $data=Helper::post($data,$this->init_url.http_build_query($params));
        $response=json_decode($data,true);
        return [
            'keys'=>$response['SyncKey']['List']
        ];
    }
    
    public function syncCheck($keys,$sign)
    {
        $data=[
            'r'=>'',
            'skey'=>$sign['skey'],
            'sid'=>$sign['sid'],
            'uin'=>$sign['uin'],
            'deviceid'=>$sign['deviceid'],
            'synckey'=>'',
            '_'=>Helper::getMillisecond()
            ];
        $synckey='';
        foreach($keys as $row){
            $synckey.=$row['key']."_".$row['val']."|";
        }
        $data['synckey']=trim($synckey,"|");
        $response=Helper::get($this->sync_check_url.http_build_query($data));
        $code=0;
        $status=0;
        if(preg_match("/retcode:\"(\d+)\",selector:\"(\d+)\"/i",$response,$match)){
            $code=$match[1];
            $status=$match[2];
        }
        return ['code'=>$code,'status'=>$status];
    }

    public function msgNotify($cookie,$myinfo)
    {
        $data=[
            'Code'=>3,
            'FromUserName'=>$myinfo['FromUserName'],
            'ToUserName'=>$myinfo['FromUserName'],
            'ClientMsgId'=>Helper::getMillisecond()
            ];
        $data=json_encode(array_merge($data,$cookie));
        $params=[
            ];
        $response=Helper::post($data,$this->notify_url.http_build_query($params));
        $obj=json_decode($response,true);
    }

    public function msgSync($cookie,$myinfo)
    {
        $data=[
            'Code'=>3,
            'FromUserName'=>$myinfo['FromUserName'],
            'ToUserName'=>$myinfo['FromUserName'],
            'ClientMsgId'=>Helper::getMillisecond()
            ];
        $data=json_encode(array_merge($data,$cookie));
        $params=[
            ];
        $response=Helper::post($data,$this->notify_url.http_build_query($params));
        $obj=json_decode($response,true);
    }
}

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
    public $get_contacts_url        ="https://wx2.qq.com/cgi-bin/mmwebwx-bin/webwxgetcontact?";
    public $notify_url              ="https://wx2.qq.com/cgi-bin/mmwebwx-bin/webwxstatusnotify?";
    public $msg_sync_url            ="https://wx2.qq.com/cgi-bin/mmwebwx-bin/webwxsync?";

    public function requestUuid()
    {
        $data=[
            'appid'=>'wx782c26e4c19acffb',
            'redirect_uri'=>'https://web.weixin.qq.com/cgi-bin/mmwebwx-bin/webwxnewloginpage',
            'fun'=>'new',
            'lang'=>'zh_CN',
            '_'=>Helper::getMillisecond(),
        ];
        $code=0;
        $uuid=0;
        $data=Helper::get($this->uuid_url.http_build_query($data));
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
            'r'=>abs(~time()),
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
        $deviceid=Helper::getDeviceId();
        $obj=[
            'cookie'=>[
                'BaseRequest'=>[
                    'Uin'=>(string)$obj->wxuin,
                    'Sid'=>(string)$obj->wxsid,
                    'Skey'=>(string)$obj->skey,
                    'DeviceID'=>$deviceid,
                ]
            ],
            'pass_ticket'=>(string)$obj->pass_ticket,
            'uin'=>(string)$obj->wxuin,
            'sid'=>(string)$obj->wxsid,
            'skey'=>(string)$obj->skey,
            'deviceid'=>$deviceid,
        ];
        return $obj;
    }

    public function init($cookie,$ticket)
    {
        $params=[
            'r'=>abs(~time()),
            'lang'=>'zh_CN',
            'pass_ticket'=>$ticket,
            '_'=>Helper::getMillisecond()
        ];
        $data=json_encode($cookie);
        $data=Helper::post($data,$this->init_url.http_build_query($params));
        $obj=json_decode($data,true);
        if($obj['BaseResponse']['Ret']==0){
            print_r($obj);
            return $obj;
        }
        else{
            Helper::msg("init code:".$obj['BaseResponse']['Ret']);
            return [];
        }
    }
    

    public function openNotify($cookie,$info)
    {
        $data=[
            'Code'=>3,
            'FromUserName'=>$info['FromUserName'],
            'ToUserName'=>$info['FromUserName'],
            'ClientMsgId'=>Helper::getMillisecond()
            ];
        $data=json_encode(array_merge($data,$cookie));
        $params=[
            'lang'=>"zh_CN",
            'pass_ticket'=>$info['ticket'],
            ];
        $response=Helper::post($data,$this->notify_url.http_build_query($params));
        $obj=json_decode($response,true);
        if($obj['BaseResponse']['Ret']==0){
            return true;
        }
        return false;
    }

    public function getContacts($data)
    {
        $params=[
            'lang'=>'zh_CN',
            'pass_ticket'=>$data['pass_ticket'],
            'r'=>Helper::getMillisecond(),
            'seq'=>0,
            'skey'=>$data['skey'],
         ];
        $response=Helper::get($this->get_contacts_url.http_build_query($params));
        $obj=json_decode($response,true);
        if($obj['BaseResponse']['Ret']==0){
            return $obj['MemberList'];
        }
        return [];
    }

    public function syncCheck($keys,$sign)
    {
        $synckey='';
        foreach($keys['List'] as $row){
            $synckey.=$row['Key']."_".$row['Val']."|";
        }
        $data=[
            'r'=>abs(~time()),
            'skey'=>$sign['skey'],
            'sid'=>$sign['sid'],
            'uin'=>$sign['uin'],
            'deviceid'=>Helper::getDeviceId(),
            'synckey'=>trim($synckey,"|"),
            '_'=>Helper::getMillisecond()
        ];

        $response=Helper::get($this->sync_check_url.http_build_query($data));
        $code=0;
        $status=0;
        if(preg_match("/retcode:\"(\d+)\",selector:\"(\d+)\"/i",$response,$match)){
            $code=$match[1];
            $status=$match[2];
        }
        return ['code'=>$code,'status'=>$status];
    }

    public function msgSync($data,$keys)
    {
        $params=$data;
        $cookies['SyncKey']=Array('Count'=>count($keys),"List"=>$keys);
        $cookies['rr']=~~time();

        $response=Helper::post(json_encode($data),$this->msg_sync_url.http_build_query($params));
        $obj=json_decode($response);
        $result=[];
        if($obj['BaseResponse']['Ret']==0 ){
            if($obj['AddMsgCount']>0){
                $result['msg']=Array();
                foreach($obj['AddMsgList'] as $row){
                    array_push($result['msg'],$row);
                } 
            }
            $result['keys']=$obj['SyncCheckKey'];
            $result['ContinueFlag']=$obj['ContinueFlag'];
        }
        return $result;
    }

}

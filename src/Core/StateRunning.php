<?php
namespace WechatBot\Core;
use WechatBot\Helper\Helper;
use WechatBot\Protocol\Protocol;
class StateRunning extends State{
    const CHECK_INTVAL=5000;
    public function init($bus)
    {
        parent::init($bus);
        $this->bus->listen(State::signal_running,$this);
    }

    public function doState()
    {
        Helper::msg('running is fire');
        $this->tickcount += $this->getTickCount();
        $this->tryFetchMsg();
    }

    private function tryFetchMsg()
    {
        if($this->tickcount > self::CHECK_INTVAL){
            $this->tickcount=0;
            $bot_data=$this->bus->getBotData();
            $data['uin']=$bot_data['uin'];
            $data['sid']=$bot_data['sid'];
            $data['deviceid']=Helper::getDeviceId();
            $data['skey']=$bot_data['skey'];
            $result=$this->protocol->syncCheck($bot_data['SyncKey'],$data);
            if($result['code']==0){
                if($result['status']==2){
                    $msg=$this->protocol->msgSync($data,$bot_data['SyncKey']['list']);
                    if($msg){
                        $bot_data['SyncKey']=$msg['keys'];
                        foreach($msg['msg'] as $m){
                            $this->translateName($m,$bot_data);
                        }
                        $this->pushMsg($m);
                    }
                }
            }
            else if($result['code']==1100){
                $this->bus->removeMe();
            }
        }
    }

    private function translateName(&$msg,$data)
    {
        $names=$data['contacts'];
        foreach($names as $role){
            if($role['UserName']==$msg['FromUserName']){
                $msg['FromUserName']=$role['NickNameName'];
            } 
        }
    }

    private function pushMsg($msg)
    {
        $data=[];
        print_r($msg);
        //$this->bus->pushMsg($data);
    }
}

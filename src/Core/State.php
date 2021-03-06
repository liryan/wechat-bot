<?php
namespace WechatBot\Core;
use WechatBot\IInterface\IStateLogic;
use WechatBot\Protocol\Protocol;
use WechatBot\Helper\Helper;
abstract class State implements  IStateLogic
{
    const signal_started=   'started';
    const signal_qrcode=    'qrcode';
    const signal_waitlogin= 'waitlogin';
    const signal_logined=   'logined';
    const signal_running=   'running';
    const signal_stopped=   'stopped';
    const signal_failed=    'failed';

    protected               $bus;
    protected               $protocol;
    public static           $signal_default;
    protected               $ticktime=0;
    protected               $tickcount=0;

    public function init($bus)
    {
        static::$signal_default=State::signal_started;
        $this->protocol=new Protocol();
        $this->bus=$bus;  
    }

    public function getTickCount()
    {
        $now=Helper::getMillisecond();
        $count=$now-$this->ticktime;
        $this->ticktime=$now;
        return $count;
    }

    public function tick()
    {
        $bot_data=$this->bus->getBotData();
        if($bot_data && isset($bot_data['deviceid'])){
            $bot_data['deviceid']="e".mt_rand(10000000,99999999).mt_rand(1000000,9999999);
        }
    }
}

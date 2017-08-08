<?php
namespace WechatBot\Core;
use WechatBot\IInterface\IStateLogic;
use WechatBot\Protocol\Protocol;
abstract class State implements  IStateLogic
{
    const signal_started='started';
    const signal_qrcode='qrcode';
    const signal_waitlogin='waitlogin';
    const signal_logined='logined';
    const signal_running='running';
    const signal_stopped='stopped';
    const signal_failed='failed';

    protected $bus;
    protected $protocol;
    public static $signal_default;
    public function init($bus)
    {
        static::$signal_default=State::signal_started;
        $this->protocol=new Protocol();
        $this->bus=$bus;  
    }
}

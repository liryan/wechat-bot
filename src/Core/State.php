<?php
namespace WechatBot\Core;
use WechatBot\IInterface\IStateLogic;
abstract class State implements  IStateLogic
{
    const signal_started='started';
    const signal_waitlogin='waitlogin';
    const signal_logined='logined';
    const signal_running='running';
    const signal_stopped='stopped';
    const signal_failed='failed';

    protected $bus;
    public static $signal_default;
    public function init($bus)
    {
        static::$signal_default=State::signal_started;
        $this->bus=$bus;  
    }

    public function fireState($signal)
    {
        return $this->bus->fire($signal);
    }

    public function listenState($signal)
    {
        return $this->bus->listen($signal,$this);
    }
}

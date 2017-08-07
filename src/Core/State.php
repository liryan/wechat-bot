<?php
namespace WechatBot\Core;
use WechatBot\IInterface\IStateLogic;
abstract class State implements  IStateLogic
{
    const signal_started='started';
    const signal_waitlogin='waitlogin';
    const signal_logined='logined';
    const signal_stopped='stopped';
    const signal_failed='failed';

    public static $signal_table=[];

    public static function start()
    {
        $states=[
            StateNone::class,
            StateLogin::class,
            StateRunning::class,
        ];

        foreach($states as $cls){
            $obj=new $cls();
            $obj->init();
        }
        static::fireState(self::signal_started);
    }

    public static function fireState($signal)
    {
        if(!isset(static::$signal_table[$signal])){
            throw new BotException("sorry,not find logic that to process this signal:$signal");
        }
        static::$signal_table[$signal]->doState();
    }

    public function listenState($signal)
    {
        if(!isset(static::$signal_table[$signal])){
            static::$signal_table[$signal]=$this;
        }
    }
}

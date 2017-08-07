<?php
namespace WechatBot\Core;

class Bus{
    private $sigtable=[];
    public function start()
    {
        $states=[
            new StateNone(),
            new StateQrcode(),
            new StateLogin(),
            new StateRunning()
        ];

        foreach($states as $obj){
            $obj->init($this);
        }

        $this->fire(State::$signal_default);
    }

    public function fire($signal)
    {
        if(!isset($this->sigtable[$signal])){
            throw new BotException("No listener for $signal");
        }
        else{
            return $this->sigtable[$signal]->doState();
        }
    }

    public function listen($signal,$who)
    {
        if(!isset($this->sigtable[$signal])){
            $this->sigtable[$signal]=$who;
        }
    }
}

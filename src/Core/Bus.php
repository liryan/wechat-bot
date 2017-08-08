<?php
namespace WechatBot\Core;
class Bus{
    const UUID_Q="wechatbot_uuid_queue";
    const SIGNAL_Q="wechatbot_signal_queue";

    private $sigtable=[];
    private $current_signal=null;
    private $queue;
    private $botid;
    private static $bus_lib=[];
    public function __construct($id,$queue)
    {
        $this->botid=$id;
        static::$bus_lib[$id]=$this;
        $this->queue=$queue;
    }

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

    public function fire($signal,$remote_uuid=0)
    {
        if($remote==false){
            $this->current_signal=$signal;
            if(!isset($this->sigtable[$signal])){
                throw new BotException("No listener for $signal");
            }
            else{
                return $this->sigtable[$signal]->doState();
            }
        }
        else{
            if($this->botid){
                $this->queue->send(SIGNAL_Q,json_encode(['uuid'=>$this->botid,'signal'=>$signal]));
            }
            else{
                throw new BotException("Sorry,uuid havn't been initialized,cannot send to remote");
            }
        }
    }

    public function listen($signal,$who)
    {
        if(!isset($this->sigtable[$signal])){
            $this->sigtable[$signal]=$who;
        }
    }

    public function checkSignal()
    {
        if($this->current_signal){
            $this->fire($this->current_signal); 
        }
    }

    public function register($uuid)
    {
        $this->botid=$uuid;
        $this->queue->push(self::UUID_Q,$uuid);
    }

    public function switchTo($signal)
    {
        $this->current_signal=$signal;
    }

    public function getBotId()
    {
        return $this->botid;
    }

    public static function isNeedRemove($id)
    {
        
    }

    public static function stopIt($id)
    {
    }
}

<?php
/**
 * Bus
 * 消息中枢，关联bot和状态处理机
 * @package 
 * @version 0.0.1
 * @copyright Open Source
 * @author liruiyan <canbetter@qq.com> 
 * @license MIT
 */
namespace WechatBot\Core;
use WechatBot\Helper\Helper;
class Bus{
    const UUID_Q="wechatbot_uuid_queue";
    const SIGNAL_Q="wechatbot_signal_queue";
    const SIGNAL_NONE="exit";

    private $sigtable=[];
    private $current_signal=null;
    private $queue;
    private $bot_slot;
    public function __construct($bot,$queue)
    {
        $this->bot_slot=$bot;
        $this->self_data=[];
        $this->queue=$queue;
    }

    /**
     * start 
     * start the first state machine
     * @access public
     * @return void
     */
    public function start($wait=true)
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
        if(!$wait){ 
            $this->fire(State::$signal_default);
        }
    }

    /**
     * fire 
     * enter other state mechine
     * @param mixed $signal 
     * @param int $remote_uuid 
     * @access public
     * @return void
     */
    public function fire($signal,$remote_uuid=0)
    {
        if($this->current_signal==self::SIGNAL_NONE){
            return;
        }
        Helper::msg("Fire signal:$signal");
        if(!$remote_uuid){
            $this->current_signal=$signal;
            if(!isset($this->sigtable[$signal])){
                throw new BotException("No listener for $signal");
            }
            else{
                return $this->sigtable[$signal]->doState();
            }
        }
        else{
            $this->queue->send(self::SIGNAL_Q,json_encode(['uuid'=>$remote_uuid,'signal'=>$signal]));
        }
    }

    /**
     * listen 
     * register signal for process
     * @param mixed $signal 
     * @param mixed $who 
     * @access public
     * @return void
     */
    public function listen($signal,$who)
    {
        if(!isset($this->sigtable[$signal])){
            $this->sigtable[$signal]=$who;
        }
    }

    /**
     * checkSignal 
     * call this in main loop
     * @access public
     * @return void
     */
    public function checkSignal()
    {
        if($this->current_signal){
            $this->fire($this->current_signal); 
        }
    }

    /**
     * register 
     * 
     * @param mixed $uuid 
     * @access public
     * @return void
     */
    public function register($uuid)
    {
        $this->queue->send(self::UUID_Q,$uuid);
    }

    /**
     * switchTo 
     * 
     * @param mixed $signal 
     * @access public
     * @return void
     */
    public function switchTo($signal)
    {
        $this->current_signal=$signal;
    }

    public function &getBotData()
    {
        return $this->bot_slot->bot_data;
    }

    public function getBotId()
    {
        return $this->bot_slot->getId();
    }


    /**
     * removeMe 
     * remove bot who is in tiemout
     * @access public
     * @return void
     */
    public function removeMe()
    {
        $this->current_signal=self::SIGNAL_NONE;
        $this->sigtable=[];
        Bot::remove($this->bot_slot,0);
    }

    /**
     * identifyOne 
     * kick one bot offline
     * @param mixed $uin 
     * @access public
     * @return void
     */
    public function identifyOne($uin)
    {
        Bot::remove($this->bot_slot,$uin);
    }

    public function push($queuename,$data)
    {
        $this->queue->send($queuename,$data);
    }
}

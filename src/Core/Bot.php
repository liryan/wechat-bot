<?php
/**
 * Bot 
 * 
 * @package 
 * @version 0.0.1
 * @copyright Open Source
 * @author liruiyan <canbetter@qq.com> 
 * @license MIT
 */

namespace WechatBot\Core;

class Bot
{
    private $bus;
    private $id;
    private $time;
    public function __construct($id,$queue)
    {
        $this->bus=new Bus($queue);
        $this->id=$id;
    }

    public function start()
    {
        $this->bus->start();
    }

    public function getId()
    {
        return $this->id;
    }

    public function tick()
    {

        $this->bus->checkSignal();
    }

    public function switchTo($signal)
    {
        $this->bus->switchTo($signal);
    }

    /**
     * buildFromRemote 
     * 创建web端扫码登录的机器人
     * @param mixed $queue 
     * @static
     * @access public
     * @return void
     */
    public static function buildFromRemote($queue)
    {
        $uuid=$queue->pop(Bus::UUID_Q);
        if($uuid){
            return new Bot($uuid,$queue);
        }
        return null;
    }

    public static function remoteSignal($queue)
    {
        $msg=$queue->pop(Bus::SIGNAL_Q);
        if($msg){
            $data=json_decode($msg,true);
            return $data;
        }
        return null;
    }

    public function isNeedRemove()
    {
        return Bus::isNeedRemove($this->id);
    }    

    public function kick($id=0)
    {
    }
}

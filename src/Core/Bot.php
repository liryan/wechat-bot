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
use WechatBot\Helper\Helper;
class Bot
{
    private $bus;
    private $id;
    private $time;

    public  $bot_data;//微信数据
    public static $factory=[];

    public function __construct($id,$queue)
    {
        $this->bus=new Bus($this,$queue);
        $this->id=$id;
    }

    public function start($wait=false)
    {
        $this->bus->start($wait);
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
        Helper::msg("signal switch to $signal");
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
            Helper::msg("create bot uuid=$uuid");
            $new_bot=new Bot($uuid,$queue);
            $new_bot->start(true);
            static::$factory[$uuid]=$new_bot;
            return $new_bot;
        }
        return null;
    }

    /**
     * remoteSignal 
     * 接受来自web端的登录信息
     * @param mixed $queue 
     * @static
     * @access public
     * @return void
     */
    public static function remoteSignal($queue)
    {
        $msg=$queue->pop(Bus::SIGNAL_Q);
        if($msg){
            $data=json_decode($msg,true);
            return $data;
        }
        return null;
    }

    /**
     * remove 
     * if [with_uin]==null, will remove [bot],else will remove other whose uin==[with_uin]
     * @param mixed $bot  
     * @param mixed $with_uin 
     * @static
     * @access public
     * @return void
     */
    public static function remove($target_bot,$with_uin)
    {
        foreach(static::$factory as $uuid=>$bot){
            if($target_bot===$bot){
                if(!$with_uin){
                    Helper::msg("remove bot it");
                    unset(static::$factory[$uuid]);
                    break;
                }
            }
            else{
                $bot_data=$bot->bot_data;
                if(isset($bot_data['uin']) && $bot_data['uin']==$with_uin){
                    Helper::msg("remove bot $uuid");
                    unset(static::$factory[$uuid]);
                    break;
                }
            }
        }
    }
}

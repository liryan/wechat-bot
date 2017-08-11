<?php
/**
 * Bots 
 * 机器人管理器 
 * @package 
 * @version 0.0.1
 * @copyright Open Source
 * @author liruiyan <canbetter@qq.com> 
 * @license MIT
 */
namespace WechatBot;
use WechatBot\Core\Bot;
use WechatBot\Helper\Queue;
use WechatBot\Helper\Helper;

class Bots
{
    private $bot_lib=[];
    private $conf;
    public function __construct($config)
    {
        $this->conf=$config;
    }

    /**
     * run 
     * 守护进程过程，检查微信消息
     * @access public
     * @return void
     */
    public function run()
    {
        $queue=new Queue($this->conf['redis']);
        $start=0;
        do{
            Bot::buildFromRemote($queue);
            $signal=Bot::remoteSignal($queue);
            if($signal){
                Helper::msg("recv:".json_encode($signal));
                if(isset(Bot::$factory[$signal['uuid']])){
                    $bot=Bot::$factory[$signal['uuid']];
                    $bot->switchTo($signal['signal']);
                }
                else{
                    Helper::msg("No bot responses for ".$signal['uuid']."'s ".$signal['signal']);
                }
            }
            foreach(Bot::$factory as $bot){
                $bot->tick();
            }
            $now=Helper::getMillisecond();
            if($now-$start>2000){
                Helper::msg("Current number of bots is ".count(Bot::$factory));
                $start=$now;
            }
            usleep(1000000);
        }while(true);
    }

    /**
     * scanQrcode 
     * 页面调用，展现扫描二维码
     * @param int $count 要启动的机器人数量
     * @access public
     * @return void
     */
    public function scanQrcode($count=1)
    {
        $queue=new Queue($this->conf['redis']);
        for($i=0;$i<$count;$i++){
            $b=new Bot($i,$queue);
            $b->start();
        }
    }
}

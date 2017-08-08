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
        do{
            $bot=Bot::buildFromRemote($queue);
            if($bot){
                $this->bot_lib[$bot->getId()]=$bot;
            }
            $signal=Bot::remoteSignal();
            if($signal){
                if(isset($this->bot_lib[$signal['uuid']])){
                    $bot=$this->bot_lib[$signal['uuid']];
                    $bot->switchTo($signal['signal']);
                }
                else{
                    Helper::msg("No bot responses for ".$signal['uuid']."'s ".$signal['signal']);
                }
            }

            foreach($this->bot_lib as $uuid=>$bot){
                if($bot->isNeedRemove()){
                    unset($this->bot_lib[$uuid]);
                }
                else{
                    $bot->tick();
                }
            }
            usleep(200);
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
            $b=new Bot($i);
            $b->start();
        }
    }
}

<?php
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
    public function run($count=1)
    {
        $queue=new Queue($this->conf['redis']);
        do{
            $bot=Bot::buildFromRemote($queue);
            if($bot){
                $this->bot_lib[$uuid]=$b;
            }
            $signal=Bot::remoteSignal();
            if($signal){
                if(isset($this->bot_lib[$signal['uuid']])){
                    $bot=$this->bot_lib[$signal['uuid']];
                    $bot->switchTo($signal['signal']);
                }
            }

            foreach($this->bot_lib as $bot){
                $bot->tick();
            }

            usleep(200);
        }while(true);
    }

    public function panel($count=1)
    {
        $queue=new Queue($this->conf['redis']);
        for($i=0;$i<$count;$i++){
            $b=new Bot($i);
            $b->start();
            $this->bot_lib[]=$b;
        }
    }
}

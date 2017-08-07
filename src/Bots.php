<?php
namespace WechatBot;
use WechatBot\Core\Bot;
class Bots
{
    private $bot_lib=[];
    public function run($count=1)
    {
    }

    public function panel($count=1)
    {
        for($i=0;$i<$count;$i++){
            $b=new Bot();
            $b->start();
            $this->bot_lib[]=$b;
        }
    }
}

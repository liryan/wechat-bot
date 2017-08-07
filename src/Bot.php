<?php
namespace WechatBot;
use WechatBot\Core\State;
class Bot
{
    public function run()
    {
        State::start();
    }
}

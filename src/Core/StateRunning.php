<?php
namespace WechatBot\Core;
class StateRunning extends State{
    public function init()
    {
        $this->listenState(State::signal_logined);
    }

    public function doState()
    {
        echo "is running";
    }
}

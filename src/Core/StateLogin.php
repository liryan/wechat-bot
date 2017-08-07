<?php
namespace WechatBot\Core;
class StateLogin extends State{
    public function init()
    {
        $this->listenState(State::signal_waitlogin);
    }
    public function doState()
    {
        echo "has logined .ok \n";
        $this->fireState(State::signal_logined);
    }
}

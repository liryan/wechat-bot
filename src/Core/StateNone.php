<?php
namespace WechatBot\Core;
class StateNone extends State{
    public function init()
    {
        $this->listenState(State::signal_started);
    }
    public function doState()
    {
        echo "none is ok,to login\n";
        $this->fireState(State::signal_waitlogin);
    }
}

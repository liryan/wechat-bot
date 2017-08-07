<?php
namespace WechatBot\Core;
class StateNone extends State{
    public function init($bus)
    {
        parent::init($bus);
        $this->listenState(State::signal_started);
    }
    public function doState()
    {
        $this->fireState(State::signal_waitlogin);
    }
}

<?php
namespace WechatBot\Core;
class StateRunning extends State{
    public function init($bus)
    {
        parent::init($bus);
        $this->listenState(State::signal_logined);
    }

    public function doState()
    {
        echo "is running";
    }
}

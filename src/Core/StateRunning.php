<?php
namespace WechatBot\Core;
class StateRunning extends State{
    public function init($bus)
    {
        parent::init($bus);
        $this->bus->listen(State::signal_logined,$this);
    }

    public function doState()
    {
    }
}

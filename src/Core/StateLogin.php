<?php
namespace WechatBot\Core;
class StateLogin extends State{
    public function init($bus)
    {
        parent::init($bus);
        $this->bus->listen(State::signal_waitlogin);
    }
    public function doState()
    {
        $this->bus->fire(State::signal_logined);
    }
}

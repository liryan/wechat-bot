<?php
namespace WechatBot\Core;
class StateNone extends State{
    public function init($bus)
    {
        parent::init($bus);
        $this->bus->listen(State::signal_started);
    }
    public function doState()
    {
        $this->bus->fire(State::signal_qrcode);
    }
}

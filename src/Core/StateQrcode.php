<?php
namespace WechatBot\Core;

class StateQrcode extends State{
    public function init($bus)
    {
        parent::init($bus);
        $this->bus->listen(State::signal_qrcode);
    }

    public function doState()
    {
        $uuid=$this->protocol->requestUuid();
        $this->bus->register($uuid);
        $this->bus->fire(State::signal_waitlogin,$remote=true);
    }
}

<?php
namespace WechatBot\Core;

class StateQrcode extends State{
    public function init($bus)
    {
        parent::init($bus);
        $this->listenState(State::signal_waitlogin);
    }
    public function doState()
    {
        echo "show qrcode<br>";
        //$data=$this->protocol_factory->createQrcode();
        //$this->protocol_factory->showQrcode();
        //$this->fireState(State::signal_logined);
    }
}

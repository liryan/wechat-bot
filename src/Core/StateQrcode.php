<?php
namespace WechatBot\Core;
use WechatBot\Helper\Helper;

class StateQrcode extends State{
    public function init($bus)
    {
        parent::init($bus);
        $this->bus->listen(State::signal_qrcode,$this);
    }

    public function doState()
    {
        $uuid=$this->protocol->requestUuid();
        if($uuid){
            $this->bus->register($uuid);
            $this->bus->fire(State::signal_waitlogin,$uuid);
            $url=$this->protocol->getQrcodeUrl($uuid);
            Helper::outImg($url);
        }
    }
}

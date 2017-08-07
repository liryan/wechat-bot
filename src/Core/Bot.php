<?php
namespace WechatBot\Core;
class Bot
{
    private $bus;
    private $id;
    private static $counter=1;
    public function __construct()
    {
        $this->bus=new Bus();
        $this->id=static::$counter++;
    }

    public function start()
    {
        $this->bus->start();
    }

    public function getId()
    {
        return $this->id;
    }
}

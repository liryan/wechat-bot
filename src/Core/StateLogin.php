<?php
/**
 * StateLogin 
 * 检查用户登录
 * @uses State
 * @package 
 * @version 0.0.1
 * @copyright Open Source
 * @author liruiyan <canbetter@qq.com> 
 * @license MIT
 */

namespace WechatBot\Core;

class StateLogin extends State{
    const   CHECK_LOGINED=      1500;
    const   FAILD_COUNT_LIMIT=  100;
    private $deltatime=         0;
    private $failed_counter=    0;
    /**
     * init 
     * 初始化状态处理器
     * @param mixed $bus 
     * @access public
     * @return void
     */
    public function init($bus)
    {
        parent::init($bus);
        $this->bus->listen(State::signal_waitlogin);
    }

    /**
     * doState 
     * 处理状态逻辑
     * @access public
     * @return void
     */
    public function doState()
    {
        $count=$this->getTickCount();
        if($count>self::CHECK_LOGINED){
            if($this->checkLoginState()){
                $this->bus->fire(State::signal_logined);
            }
            else{
                $this->failed_counter++;
                if($this->failed_counter>self::FAILD_COUNT_LIMIT){
                    $this->bus->kick();
                }
            }
        }
    }

    /**
     * checkLoginState 
     * 检查登录状态
     * @access private
     * @return void
     */
    private function checkLoginState()
    {
        $code=$this->Protocol->getLoginCode($this->bus->getBotId());
        if($code==Protocol::CODE_LOGINED){
            $info=Protocol::getUserInfo();
            //TODO 重复登录的处理
            $this->bus->kick($wechatId);
            return true;
        }
        else if($code==Protocol::CODE_SCANED){
            return false;
        }
    }
}

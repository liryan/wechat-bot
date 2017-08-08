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
    const   CHECK_LOGINED       =1000;
    const   FAILD_COUNT_LIMIT   =100;

    const   STEP_START          =0;
    const   STEP_INIT           =1;
    const   STEP_COOKIE         =2;
    const   STEP_NOTIFY         =3;

    private $deltatime=         0;
    private $failed_counter=    0;
    private $cur_step;

    /**
     * init 
     * 初始化状态处理器
     * @param mixed $bus 
     * @access public
     * @return void
     */
    public function init($bus)
    {
        $this->cur_step=self::STEP_START;
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
        switch($this->cur_step){
        case self::STEP_NONE:
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
            break;
        case self::STEP_INIT:
            $this->getContractList();
            break;
        case self::STEP_NOTIFY:
            break;
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
        $logininfo=$this->protocol->getLoginCode($this->bus->getBotId());
        $code=$logininfo['code'];
        if($code==Protocol::CODE_LOGINED){
            $data=$this->protocol->getCookie($logininfo['url']);
            $bot_data=&$this->bus->getBotData();
            $bot_data = $data;
            $this->bus->identifyOne($this->uid);
            $this->cur_step=self::STEP_INIT;
            return true;
        }
        else if($code==Protocol::CODE_SCANED){
            return false;
        }
        else if($code==Protocol::CODE_TIMEOUT){
            return false;
        }
    }

    public function getContractList()
    {
        $bot_data=&$this->bus->getBotData();
        $data=$this->protocol->init($bot_data['cookie'],$bot_data['pass_ticket']);
    }
}

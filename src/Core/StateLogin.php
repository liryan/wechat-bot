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
use WechatBot\Helper\Helper;
use WechatBot\Protocol\Protocol;
class StateLogin extends State{
    const   CHECK_LOGINED       =5000;
    const   FAILD_COUNT_LIMIT   =100;

    const   STEP_START          =0;
    const   STEP_INIT           =1;
    const   STEP_COOKIE         =2;
    const   STEP_NOTIFY         =3;

    private $deltatime          =0;
    private $failed_counter     =0;
    private $cur_step;
    private $pause              =false;             

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
        $this->bus->listen(State::signal_waitlogin,$this);
    }

    /**
     * doState 
     * 处理状态逻辑
     * @access public
     * @return void
     */
    public function doState()
    {
        $this->tickcount += $this->getTickCount();
        Helper::msg("State:".$this->cur_step.":".$this->tickcount);
        switch($this->cur_step){
        case self::STEP_START:
            if($this->pause){
                Helper::msg('Login statue has pause');
                return;
            }
            if($this->tickcount>self::CHECK_LOGINED){
                $this->tickcount=0;
                if(!$this->checkLoginState()){
                    $this->failed_counter++;
                    if($this->failed_counter>self::FAILD_COUNT_LIMIT){
                        $this->pause=true;
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
        Helper::msg("Code:".$code);
        if($code==Protocol::CODE_LOGINED){
            $data=$this->protocol->getCookie($logininfo['url']);
            $bot_data=&$this->bus->getBotData();
            $bot_data = $data;
            $this->bus->identifyOne($bot_data['uin']);
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
        print_r($bot_data);
        $data=$this->protocol->init($bot_data['cookie'],$bot_data['pass_ticket']);
        print_r($data);
        exit();
    }
}

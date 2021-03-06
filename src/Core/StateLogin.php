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
    const   CHECK_LOGINED       =10000;
    const   FAILD_COUNT_LIMIT   =100;

    const   STEP_START          =0;
    const   STEP_INIT           =1;
    const   STEP_CONTACT        =2;
    const   STEP_OVER           =3;

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
            $this->wxInit();
            break;
        case self::STEP_CONTACT;
            $this->getContacts();
            break;
        case self::STEP_OVER:
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
            Helper::msg($bot_data['uin']." has logined");
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

    public function getContacts()
    {
        $bot_data=&$this->bus->getBotData();
        $contacts=$this->protocol->getContacts(Array('pass_ticket'=>$bot_data['pass_ticket'],'skey'=>$bot_data['skey']));
        if($contacts){
             $bot_data['contacts']=$contacts;
             $this->cur_step=self::STEP_OVER;
             $this->bus->fire(State::signal_running);
             return true;
        }
        $this->cur_step=self::STEP_OVER;
        return false;
    }

    public function wxInit()
    {
        $bot_data=&$this->bus->getBotData();
        $data=$this->protocol->init($bot_data['cookie'],$bot_data['pass_ticket']);
        if($data){
            $bot_data['User']=$data['User'];
            $bot_data['SyncKey']=$data['SyncKey'];
            if($this->openNotify()){
                Helper::msg("notify successfully");
                $this->cur_step=self::STEP_CONTACT;
            }
        }
        else{
            Helper::msg("init weixin client failed");
            $this->cur_step=self::STEP_OVER;
        }
    }

    public function openNotify()
    {
        $bot_data=&$this->bus->getBotData();
        $data=Array(
            'ticket'=>$bot_data['pass_ticket'],
            'FromUserName'=>$bot_data['User']['UserName']
        );
        return $this->protocol->openNotify($bot_data['cookie'],$data);
    }

}

<?php
/**
 * IStateLogic 
 * 状态逻辑接口
 * @package 
 * @version 0.0.1
 * @copyright Open Source
 * @author liruiyan <canbetter@qq.com> 
 * @license MIT
 */
namespace WechatBot\IInterface;
interface IStateLogic
{
    public function init($bus);
    public function doState();
}

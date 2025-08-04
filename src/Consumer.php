<?php
/**
 * @Author SSH
 * @Email 694711507@qq.com
 * @Date 2025/8/5 00:15
 * @Description
 */
namespace ssh\Stomp;


/**
 * Interface Consumer
 * @package ssh\Stomp
 */
interface Consumer
{
    public function consume($data);
}
<?php
namespace WechatBot\Helper;
class Queue
{
    private $client;
    public function __construct($config)
    {
        $this->client=new \Predis\Client(
            [
                'scheme'=>'tcp',
                'host'=>$config['host'],
                'port'=>$config['port']
            ]
        );

        if($config['password'])
            $this->client->auth($config['password']);
        if($config['database'])
            $this->client->select($config['database']);
    }

    public function send($qname,$msg)
    {
        $this->client->rpush($qname,$msg);
    }

    public function save($tablename,$data)
    {
        $this->client->rpush($tablename,json_encode($data)); 
    }

    public function getCount($tablename)
    {
        return $this->client->llen($tablename);
    }

    public function getList($tablename,$start,$count)
    {
        $data=$this->client->lrange($tablename,$start,$count); 
        $result=[];
        foreach($data as $row){
            $result[]=json_decode($row,true);
        }
        return $result;
    }

    public function pop($qname)
    {
        return $this->client->lpop($qname);
    }
}

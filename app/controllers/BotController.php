<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Bot
 *
 * @author Abdou
 */
class BotController extends Controller implements iBot{
    
    protected $gateway = 'http://okanime.com/';
    protected $provider = '';
    
    protected $url;
    protected $doc;
    protected $current;
    
    public function url($current=false,$gateway=false){
        if($gateway)
            $this->gateway = $gateway;
        if($current)
            $this->current = $current;
        $this->url = $this->gateway . $current;
        return $this->url;
    }
    
    public function setup($current,$gateway=false){
        if($gateway)
            $this->gateway = $gateway;
        $url = $this->url($current,$gateway);
        $this->doc = phpQuery::newDocumentFileHTML($this->url);
        return $this;
    }
    
    public function pages(){
        $url = $this->url();
        $this->doc = phpQuery::newDocumentFileHTML($this->url);
        try{
            $last = pq('.wp-pagenavi a:last')->attr('href');
            $last = str_replace([$url,'page','/'],'',$last);
            $last = (int)$last;
            $pages = [];
            for($i=1;$i<=$last;$i++) $pages[] = $this->url.'page/'.$i.'/';
            return $pages;
        } catch (Exception $ex) {
            throw new Symfony\Component\Debug\Exception\OutOfMemoryException('Timeout request for : '.$this->url);
        }
    }
    
}
